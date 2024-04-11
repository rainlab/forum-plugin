<?php namespace RainLab\Forum\Components;

use Auth;
use Mail;
use Flash;
use Request;
use Redirect;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use ApplicationException;
use RainLab\Forum\Models\Member as MemberModel;
use RainLab\User\Models\User as UserModel;
use RainLab\User\Models\MailBlocker;
use Exception;

/**
 * ForumMember component displays a forum member details
 */
class ForumMember extends ComponentBase
{
    /**
     * @var RainLab\Forum\Models\Member member cache
     */
    protected $member = null;

    /**
     * @var RainLab\Forum\Models\Member otherMember cache
     */
    protected $otherMember = null;

    /**
     * @var array mailPreferences cache
     */
    protected $mailPreferences = null;

    /**
     * @var string topicPage reference to the page name for linking to topics.
     */
    public $topicPage;

    /**
     * @var string channelPage reference to the page name for linking to channels.
     */
    public $channelPage;

    /**
     * componentDetails
     */
    public function componentDetails()
    {
        return [
            'name' => "Member",
            'description' => "Displays form member information and activity."
        ];
    }

    /**
     * defineProperties
     */
    public function defineProperties()
    {
        return [
            'slug' => [
                'title' => "Slug param name",
                'description' => "The URL route parameter used for looking up the forum member by their slug. A hard coded slug can also be used.",
                'default' => '{{ :slug }}',
                'type' => 'string'
            ],
            'viewMode' => [
                'title' => "View mode",
                'description' => "Manually set the view mode for the member component.",
                'type' => 'dropdown',
                'default' => ''
            ],
            'channelPage' => [
                'title' => "Channel Page",
                'description' => "Page name to use for clicking on a Channel.",
                'type' => 'dropdown',
                'group' => 'Links',
            ],
            'topicPage' => [
                'title' => "Topic Page",
                'description' => "Page name to use for clicking on a conversation topic.",
                'type' => 'dropdown',
                'group' => 'Links',
            ],
            'includeStyles' => [
                'title' => "Enable CSS",
                'description' => "Include the CSS files with default styles for the forum",
                'type' => 'checkbox',
                'default' => true
            ],
        ];
    }

    /**
     * getViewModeOptions
     */
    public function getViewModeOptions()
    {
        return ['' => '- none -', 'view' => 'View', 'edit' => 'Edit'];
    }

    /**
     * getPropertyOptions
     */
    public function getPropertyOptions($property)
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * onRun
     */
    public function onRun()
    {
        if ($this->property('includeStyles', true)) {
            $this->addCss('assets/css/forum.css');
        }

        $this->prepareVars();
    }

    /**
     * prepareVars
     */
    protected function prepareVars()
    {
        $this->page['member'] = $this->getMember();
        $this->page['otherMember'] = $this->getOtherMember();
        $this->page['mailPreferences'] = $this->getMailPreferences();
        $this->page['canEdit'] = $this->canEdit();
        $this->page['mode'] = $this->getMode();

        // Page links
        $this->topicPage = $this->page['topicPage'] = $this->property('topicPage');
        $this->channelPage = $this->page['channelPage'] = $this->property('channelPage');
    }

    /**
     * getRecentPosts
     */
    public function getRecentPosts()
    {
        $member = $this->getMember();
        $posts = $member->posts()->with('topic')->limit(10)->get();

        $posts->each(function($post) {
            $post->topic->setUrl($this->topicPage, $this->controller);
        });

        return $posts;
    }

    /**
     * getMember
     */
    public function getMember()
    {
        if ($this->member !== null) {
            return $this->member;
        }

        if (!$slug = $this->property('slug')) {
            $member = MemberModel::getFromUser();
        }
        else {
            $member = MemberModel::whereSlug($slug)->first();
        }

        return $this->member = $member;
    }

    /**
     * getOtherMember
     */
    public function getOtherMember()
    {
        if ($this->otherMember !== null) {
            return $this->otherMember;
        }

        return $this->otherMember = MemberModel::getFromUser();
    }

    /**
     * getMailPreferences
     */
    public function getMailPreferences()
    {
        if ($this->mailPreferences !== null) {
            return $this->mailPreferences;
        }

        $member = $this->getMember();
        if (!$member || !$member->user) {
            return [];
        }

        $preferences = [];
        $blocked = MailBlocker::checkAllForUser($member->user);
        foreach ($this->getMailTemplates() as $alias => $template) {
            $preferences[$alias] = !in_array($template, $blocked);
        }

        return $this->mailPreferences = $preferences;
    }

    /**
     * getMode
     */
    public function getMode()
    {
        return $this->property('viewMode') ?: input('mode', 'view');
    }

    /**
     * canEdit
     */
    public function canEdit()
    {
        if ($this->property('viewMode') == 'view') {
            return false;
        }

        if (!$member = $this->getMember()) {
            return false;
        }

        return $member->canEdit(MemberModel::getFromUser());
    }

    /**
     * onUpdate
     */
    public function onUpdate()
    {
        try {
            if (!$this->canEdit()) {
                throw new ApplicationException('Permission denied.');
            }

            $member = $this->getMember();
            if (!$member) {
                return;
            }

            /*
             * Process mail preferences
             */
            if ($member->user) {
                MailBlocker::setPreferences(
                    $member->user,
                    post('MailPreferences'),
                    ['aliases' => $this->getMailTemplates()]
                );
            }

            /*
             * Save member
             */
            $data = array_except(post(), 'MailPreferences');
            $member->fill($data);
            $member->save();

            Flash::success(post('flash', 'Settings successfully saved!'));

            return $this->redirectToSelf();
        }
        catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
    }

    /**
     * getMailTemplates
     */
    protected function getMailTemplates()
    {
        return ['topic_reply' => 'rainlab.forum:topic_reply'];
    }

    /**
     * onPurgePosts
     */
    public function onPurgePosts()
    {
        try {
            $otherMember = $this->getOtherMember();
            if (!$otherMember || !$otherMember->is_moderator) {
                throw new ApplicationException('Access denied');
            }

            if ($member = $this->getMember()) {
                foreach ($member->posts as $post) {
                    $post->delete();
                }
            }

            Flash::success(post('flash', 'Posts deleted!'));

            return $this->redirectToSelf();
        }
        catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
    }

    /**
     * onApprove
     */
    public function onApprove()
    {
        $otherMember = $this->getOtherMember();
        if (!$otherMember || !$otherMember->is_moderator) {
            throw new ApplicationException('Access denied');
        }

        if ($member = $this->getMember()) {
            $member->approveMember();
        }

        $this->prepareVars();
    }

    /**
     * onBan
     */
    public function onBan()
    {
        $otherMember = $this->getOtherMember();
        if (!$otherMember || !$otherMember->is_moderator) {
            throw new ApplicationException('Access denied');
        }

        if ($member = $this->getMember()) {
            $member->banMember();
        }

        $this->prepareVars();
    }

    /**
     * onReport
     */
    public function onReport()
    {
        if (!Auth::check()) {
            throw new ApplicationException('You must be logged in to perform this action!');
        }

        Flash::success(post('flash', 'User has been reported for spamming, thank-you for your assistance!'));

        $moderators = UserModel::whereHas('forum_member', function($member) {
            $member->where('is_moderator', true);
        })->lists('name', 'email');

        if ($moderators) {
            $member = $this->getMember();
            $memberUrl = $this->currentPageUrl(['slug' => $member->slug]);
            $otherMember = $this->getOtherMember();
            $otherMemberUrl = $this->currentPageUrl(['slug' => $otherMember->slug]);
            $params = [
                'member' => $member,
                'memberUrl' => $memberUrl,
                'otherMember' => $otherMember,
                'otherMemberUrl' => $otherMemberUrl,
            ];
            Mail::sendTo($moderators, 'rainlab.forum:member_report', $params);
        }

        return $this->redirectToSelf();
    }

    /**
     * redirectToSelf
     */
    protected function redirectToSelf()
    {
        if (!$member = $this->getMember()) {
            return false;
        }

        // Redirect to the intended page after successful update
        $redirectUrl = post('redirect', $this->currentPageUrl([
            'slug' => $member->slug
        ]));

        return Redirect::to($redirectUrl);
    }
}

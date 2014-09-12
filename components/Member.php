<?php namespace RainLab\Forum\Components;

use Flash;
use Redirect;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Member as MemberModel;
use RainLab\User\Models\MailBlocker;
use Exception;

class Member extends ComponentBase
{

    /**
     * @var RainLab\Forum\Models\Member Member cache
     */
    protected $member = null;

    /**
     * @var array Mail preferences cache
     */
    protected $mailPreferences = null;

    /**
     * @var string Reference to the page name for linking to topics.
     */
    public $topicPage;

    /**
     * @var string Reference to the page name for linking to channels.
     */
    public $channelPage;

    public function componentDetails()
    {
        return [
            'name'        => 'Member',
            'description' => 'Displays form member information and activity.'
        ];
    }

    public function defineProperties()
    {
        return [
            'idParam' => [
                'title'       => 'Slug param name',
                'description' => 'The URL route parameter used for looking up the forum member by their slug. A hard coded slug can also be used.',
                'default'     => ':slug',
                'type'        => 'string'
            ],
            'viewMode' => [
                'title'       => 'View mode',
                'description' => 'Manually set the view mode for the member component.',
                'type'        => 'dropdown',
                'default'     => ''
            ],
            'channelPage' => [
                'title'       => 'Channel page',
                'description' => 'Page name to use for clicking on a channel.',
                'type'        => 'dropdown',
                'group'       => 'Links',
            ],
            'topicPage' => [
                'title'       => 'Topic page',
                'description' => 'Page name to use for clicking on a conversation topic.',
                'type'        => 'dropdown',
                'group'       => 'Links',
            ],
        ];
    }

    public function getViewModeOptions()
    {
        return ['' => '- none -', 'view' => 'View', 'edit' => 'Edit'];
    }

    public function getPropertyOptions($property)
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->addCss('/plugins/rainlab/forum/assets/css/forum.css');

        $this->page['member'] = $this->getMember();
        $this->page['mailPreferences'] = $this->getMailPreferences();
        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->page['canEdit'] = $this->canEdit();
        $this->page['mode'] = $this->getMode();

        /*
         * Page links
         */
        $this->topicPage = $this->page['topicPage'] = $this->property('topicPage');
        $this->channelPage = $this->page['channelPage'] = $this->property('channelPage');
    }

    public function getMember()
    {
        if ($this->member !== null)
            return $this->member;

        if (!$slug = $this->propertyOrParam('idParam'))
            $member = MemberModel::getFromUser();
        else
            $member = MemberModel::whereSlug($slug)->first();

        return $this->member = $member;
    }

    public function getMailPreferences()
    {
        if ($this->mailPreferences !== null)
            return $this->mailPreferences;

        $member = $this->getMember();
        if (!$member || !$member->user)
            return [];

        $preferences = [];
        $blocked = MailBlocker::checkAllForUser($member->user);
        foreach ($this->getMailTemplates() as $alias => $template) {
            $preferences[$alias] = !in_array($template, $blocked);
        }

        return $this->mailPreferences = $preferences;
    }

    public function getMode()
    {
        return $this->property('viewMode', post('mode', 'view'));
    }

    public function canEdit()
    {
        if ($this->property('viewMode') == 'view')
            return false;

        if (!$member = $this->getMember())
            return false;

        return $member->canEdit(MemberModel::getFromUser());
    }

    public function onUpdate()
    {
        try {
            if (!$this->canEdit())
                throw new ApplicationException('Permission denied.');

            $member = $this->getMember();
            if (!$member) return;

            /*
             * Process mail preferences
             */
            if ($member->user) {
                MailBlocker::toggleBlocks(
                    post('MailPreferences'),
                    $member->user,
                    $this->getMailTemplates()
                );
            }

            /*
             * Save member
             */
            $data = array_except(post(), 'MailPreferences');
            $member->save($data);

            Flash::success(post('flash', 'Settings successfully saved!'));

            /*
             * Redirect to the intended page after successful update
             */
            $redirectUrl = post('redirect', $this->currentPageUrl([
                'slug' => $member->slug
            ]));

            return Redirect::to($redirectUrl);
        }
        catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
    }

    protected function getMailTemplates()
    {
        return ['topic_reply' => 'rainlab.forum::mail.topic_reply'];
    }

}
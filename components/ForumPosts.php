<?php namespace RainLab\Forum\Components;

use Flash;
use Request;
use Redirect;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Post as PostModel;
use RainLab\Forum\Models\Member as MemberModel;
use ApplicationException;
use Exception;

/**
 * ForumPosts displays a list of all posts.
 */
class ForumPosts extends ComponentBase
{
    /**
     * @var RainLab\Forum\Models\Post posts
     */
    public $posts;

    /**
     * @var RainLab\Forum\Models\Member member cache
     */
    protected $member = null;

    /**
     * @var RainLab\Forum\Models\Member otherMember cache
     */
    protected $otherMember = null;

    /**
     * @var string memberPage reference to the page name for linking to members.
     */
    public $memberPage;

    /**
     * @var string topicPage reference to the page name for linking to topics.
     */
    public $topicPage;

    /**
     * @var int postsPerPage number of posts to display per page.
     */
    public $postsPerPage;

    public function componentDetails()
    {
        return [
            'name' => 'rainlab.forum::lang.posts.component_name',
            'description' => 'rainlab.forum::lang.posts.component_description',
        ];
    }

    /**
     * defineProperties
     */
    public function defineProperties()
    {
        return [
            'memberPage' => [
                'title' => 'rainlab.forum::lang.member.page_name',
                'description' => 'rainlab.forum::lang.member.page_help',
                'type' => 'dropdown'
            ],
            'topicPage' => [
                'title' => 'rainlab.forum::lang.topic.page_name',
                'description' => 'rainlab.forum::lang.topic.page_help',
                'type' => 'dropdown',
            ],
            'postsPerPage' =>  [
                'title' => 'rainlab.forum::lang.posts.per_page',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'rainlab.forum::lang.posts.per_page_validation',
                'default' => '20',
            ],
            'includeStyles' => [
                'title' => 'rainlab.forum::lang.components.general.properties.includeStyles',
                'description' => 'rainlab.forum::lang.components.general.properties.includeStyles_desc',
                'type' => 'checkbox',
                'default' => true
            ],
        ];
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

        return $this->preparePostList();
    }

    /**
     * prepareVars
     */
    protected function prepareVars()
    {
        $this->page['otherMember'] = $this->getOtherMember();

        /*
         * Page links
         */
        $this->topicPage = $this->page['topicPage'] = $this->property('topicPage');
        $this->memberPage = $this->page['memberPage'] = $this->property('memberPage');
        $this->postsPerPage = $this->page['postsPerPage'] = $this->property('postsPerPage');
    }

    /**
     * preparePostList
     */
    protected function preparePostList()
    {
        $currentPage = (int) input('page');
        $searchString = trim(input('search'));

        $posts = PostModel::with('member', 'topic');
        $posts = $posts->whereHas('member', function($member) {
            $member->where('is_approved', false);
            $member->where('is_banned', false);
        });

        $posts = $posts->listFrontEnd([
            'page' => $currentPage,
            'perPage' => $this->postsPerPage,
            'sort' => 'created_at',
            'direction' => 'desc',
            'search' => $searchString,
        ]);

        // Add a "url" helper attribute for linking to each topic
        $posts->each(function($post) {
            if ($post->topic) {
                $post->topic->setUrl($this->topicPage, $this->controller);
            }

            if ($post->member) {
                $post->member->setUrl($this->memberPage, $this->controller);
            }
        });

        // Signed in member
        $this->page['member'] = $this->member = MemberModel::getFromUser();

        if ($this->member) {
            $this->member->setUrl($this->memberPage, $this->controller);
        }

        $this->page['posts'] = $this->posts = $posts;

        // Pagination
        if ($posts) {
            $queryArr = [];
            if ($searchString) {
                $queryArr['search'] = $searchString;
            }
            $queryArr['page'] = '';
            $paginationUrl = Request::url() . '?' . http_build_query($queryArr);

            if ($currentPage > ($lastPage = $posts->lastPage()) && $currentPage > 1) {
                return Redirect::to($paginationUrl . $lastPage);
            }

            $this->page['paginationUrl'] = $paginationUrl;
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

        $post = PostModel::find(post('post'));

        if (!$post || !$post->canEdit()) {
            throw new ApplicationException('Permission denied.');
        }

        if ($member = $post->member) {
            $member->approveMember();
        }

        $this->prepareVars();

        return $this->preparePostList();

    }

    /**
     * onFlagSpam
     */
    public function onFlagSpam()
    {
        try {
            $otherMember = $this->getOtherMember();
            if (!$otherMember || !$otherMember->is_moderator) {
                throw new ApplicationException('Access denied');
            }

            $post = PostModel::find(post('post'));

            if (!$post || !$post->canEdit()) {
                throw new ApplicationException('Permission denied.');
            }

            if ($member = $post->member) {
                foreach ($member->posts as $post) {
                    $post->delete();
                }

                $member->banMember();
            }

            $this->prepareVars();

            return $this->preparePostList();
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex; else Flash::error($ex->getMessage());
        }
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
}

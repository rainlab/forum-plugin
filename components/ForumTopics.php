<?php namespace RainLab\Forum\Components;

use Request;
use Redirect;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Topic as TopicModel;
use RainLab\Forum\Models\Member as MemberModel;
use RainLab\Forum\Classes\TopicTracker;

/**
 * ForumTopics component displays a list of all topics.
 */
class ForumTopics extends ComponentBase
{
    /**
     * @var RainLab\Forum\Models\Topic topics
     */
    public $topics;

    /**
     * @var RainLab\Forum\Models\Member member cache
     */
    protected $member = null;

    /**
     * @var string memberPage reference to the page name for linking to members.
     */
    public $memberPage;

    /**
     * @var string topicPage reference to the page name for linking to topics.
     */
    public $topicPage;

    /**
     * @var int topicsPerPage number of topics to display per page.
     */
    public $topicsPerPage;

    /**
     * componentDetails
     */
    public function componentDetails()
    {
        return [
            'name' => "Topic List",
            'description' => "Displays a list of all topics.",
        ];
    }

    /**
     * defineProperties
     */
    public function defineProperties()
    {
        return [
            'memberPage' => [
                'title' => "Member Page",
                'description' => "Page name to use for clicking on a Member.",
                'type' => 'dropdown'
            ],
            'topicPage' => [
                'title' => "Topic Page",
                'description' => "Page name to use for clicking on a conversation topic.",
                'type' => 'dropdown',
            ],
            'topicsPerPage' => [
                'title' => "Topics per page",
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => "Invalid format of the topics per page value",
                'default' => '20',
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

        return $this->prepareTopicList();
    }

    /**
     * prepareVars
     */
    protected function prepareVars()
    {
        $this->topicPage = $this->page['topicPage'] = $this->property('topicPage');
        $this->memberPage = $this->page['memberPage'] = $this->property('memberPage');
        $this->topicsPerPage = $this->page['topicsPerPage'] = $this->property('topicsPerPage');
    }

    /**
     * prepareTopicList
     */
    protected function prepareTopicList()
    {
        $currentPage = input('page');
        $searchString = trim(input('search'));
        $topics = TopicModel::with('last_post_member')->listFrontEnd([
            'page' => $currentPage,
            'perPage' => $this->topicsPerPage,
            'sort' => 'updated_at',
            'search' => $searchString,
        ]);

        // Add a "url" helper attribute for linking to each topic
        $topics->each(function($topic) {
            $topic->setUrl($this->topicPage, $this->controller);

            if ($topic->last_post_member) {
                $topic->last_post_member->setUrl($this->memberPage, $this->controller);
            }

            if ($topic->start_member) {
                $topic->start_member->setUrl($this->memberPage, $this->controller);
            }
        });

        // Signed in member
        $this->page['member'] = $this->member = MemberModel::getFromUser();

        if ($this->member) {
            $this->member->setUrl($this->memberPage, $this->controller);
            $topics = TopicTracker::instance()->setFlagsOnTopics($topics, $this->member);
        }

        $this->page['topics'] = $this->topics = $topics;

        // Pagination
        if ($topics) {
            $queryArr = [];
            if ($searchString) {
                $queryArr['search'] = $searchString;
            }
            $queryArr['page'] = '';
            $paginationUrl = Request::url() . '?' . http_build_query($queryArr);

            if ($currentPage > ($lastPage = $topics->lastPage()) && $currentPage > 1) {
                return Redirect::to($paginationUrl . $lastPage);
            }

            $this->page['paginationUrl'] = $paginationUrl;
        }
    }
}

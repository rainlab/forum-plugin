<?php namespace RainLab\Forum\Components;

use Auth;
use Request;
use Redirect;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\TopicWatch;
use RainLab\Forum\Models\Topic as TopicModel;
use RainLab\Forum\Models\Member as MemberModel;

/**
 * Topic list component
 * 
 * Displays a list of all topics.
 */
class Topics extends ComponentBase
{

    /**
     * @var RainLab\Forum\Models\Member Member cache
     */
    protected $member = null;

    public function componentDetails()
    {
        return [
            'name'           => 'rainlab.forum::lang.topics.component_name',
            'description'    => 'rainlab.forum::lang.topics.component_description',
        ];
    }

    public function defineProperties()
    {
        return [
            'memberPage' => [
                'title'       => 'rainlab.forum::lang.member.page_name',
                'description' => 'rainlab.forum::lang.member.page_help',
                'type'        => 'dropdown'
            ],
            'topicPage' => [
                'title'       => 'rainlab.forum::lang.topic.page_name',
                'description' => 'rainlab.forum::lang.topic.page_help',
                'type'        => 'dropdown',
            ],
        ];
    }

    public function getPropertyOptions($property)
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->addCss('/plugins/rainlab/forum/assets/css/forum.css');

        $this->prepareVars();
        return $this->prepareTopicList();
    }

    protected function prepareVars()
    {
        /*
         * Page links
         */
        $this->topicPage = $this->page['topicPage'] = $this->property('topicPage');
        $this->memberPage = $this->page['memberPage'] = $this->property('memberPage');
    }

    protected function prepareTopicList()
    {
        $currentPage = post('page');
        $searchString = trim(post('search'));
        $topics = TopicModel::with('last_post_member')->listFrontEnd($currentPage, 'updated_at', null, $searchString);

        /*
         * Add a "url" helper attribute for linking to each topic
         */
        $topics->each(function($topic){
            $topic->setUrl($this->topicPage, $this->controller);

            if ($topic->last_post_member)
                $topic->last_post_member->setUrl($this->memberPage, $this->controller);
        });

        /*
         * Signed in member
         */
        $this->page['member'] = $this->member = MemberModel::getFromUser();
        if ($this->member) {
            $this->member->setUrl($this->memberPage, $this->controller);
            $topics = TopicWatch::setFlagsOnTopics($topics, $this->member);
        }

        $this->page['topics'] = $this->topics = $topics;

        /*
         * Pagination
         */
        if ($topics) {
            $queryArr = [];
            if ($searchString) $queryArr['search'] = $searchString;
            $queryArr['page'] = '';
            $paginationUrl = Request::url() . '?' . http_build_query($queryArr);

            if ($currentPage > ($lastPage = $topics->getLastPage()) && $currentPage > 1)
                return Redirect::to($paginationUrl . $lastPage);

            $this->page['paginationUrl'] = $paginationUrl;
        }
    }

}
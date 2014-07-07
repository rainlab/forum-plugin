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

    private $member;

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
            'memberPageIdParam' => [
                'title'       => 'rainlab.forum::lang.member.param_name',
                'description' => 'rainlab.forum::lang.member.param_help',
                'type'        => 'string',
                'default'     => ':slug',
            ],
            'topicPage' => [
                'title'       => 'rainlab.forum::lang.topic.page_name',
                'description' => 'rainlab.forum::lang.topic.page_help',
                'type'        => 'dropdown',
            ],
            'topicPageIdParam' => [
                'title'       => 'rainlab.forum::lang.topic.param_name',
                'description' => 'rainlab.forum::lang.topic.param_help',
                'type'        => 'string',
                'default'     => ':slug',
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

        return $this->prepareTopicList();
    }

    protected function prepareTopicList()
    {
        $currentPage = post('page');
        $searchString = trim(post('search'));
        $topics = TopicModel::make()->listFrontEnd($currentPage, 'updated_at', null, $searchString);

        $this->page['member'] = $this->member = MemberModel::getFromUser();
        if ($this->member)
            $topics = TopicWatch::setFlagsOnTopics($topics, $this->member);

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

        /*
         * Page links
         */
        $this->topicPage = $this->page['topicPage'] = $this->property('topicPage');
        $this->topicPageIdParam = $this->page['topicPageIdParam'] = $this->property('topicPageIdParam');
        $this->memberPage = $this->page['memberPage'] = $this->property('memberPage');
        $this->memberPageIdParam = $this->page['memberPageIdParam'] = $this->property('memberPageIdParam');
    }

}
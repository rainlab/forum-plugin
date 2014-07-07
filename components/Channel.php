<?php namespace RainLab\Forum\Components;

use Auth;
use Request;
use Redirect;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\TopicWatch;
use RainLab\Forum\Models\ChannelWatch;
use RainLab\Forum\Models\Topic as TopicModel;
use RainLab\Forum\Models\Channel as ChannelModel;
use RainLab\Forum\Models\Member as MemberModel;

/**
 * Channel component
 * 
 * Displays a list of posts belonging to a channel.
 */
class Channel extends ComponentBase
{
    /**
     * @var boolean Determine if this component is being used by the EmbedChannel component.
     */
    public $embedMode = false;

    private $member = null;
    private $channel = null;

    public $memberPage;
    public $memberPageIdParam;
    public $topicPage;
    public $topicPageIdParam;

    /**
     * @var Collection Topics cache for Twig access.
     */
    public $topics = null;

    public function componentDetails()
    {
        return [
            'name'           => 'rainlab.forum::lang.channel.component_name',
            'description'    => 'rainlab.forum::lang.channel.component_description',
        ];
    }

    public function defineProperties()
    {
        return [
            'idParam' => [
                'title'       => 'Slug param name',
                'description' => 'The URL route parameter used for looking up the channel by its slug. A hard coded slug can also be used.',
                'default'     => ':slug',
                'type'        => 'string',
            ],
            'memberPage' => [
                'title'       => 'rainlab.forum::lang.member.page_name',
                'description' => 'rainlab.forum::lang.member.page_help',
                'type'        => 'dropdown',
                'group'       => 'Links',
            ],
            'memberPageIdParam' => [
                'title'       => 'rainlab.forum::lang.member.param_name',
                'description' => 'rainlab.forum::lang.member.param_help',
                'type'        => 'string',
                'default'     => ':slug',
                'group'       => 'Links',
            ],
            'topicPage' => [
                'title'       => 'rainlab.forum::lang.topic.page_name',
                'description' => 'rainlab.forum::lang.topic.page_help',
                'type'        => 'dropdown',
                'group'       => 'Links',
            ],
            'topicPageIdParam' => [
                'title'       => 'rainlab.forum::lang.topic.param_name',
                'description' => 'rainlab.forum::lang.topic.param_help',
                'type'        => 'string',
                'default'     => ':slug',
                'group'       => 'Links',
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

        $this->page['channel'] = $this->getChannel();
        return $this->prepareTopicList();
    }

    public function getChannel()
    {
        if ($this->channel !== null)
            return $this->channel;

        if (!$slug = $this->propertyOrParam('idParam'))
            return null;

        return $this->channel = ChannelModel::whereSlug($slug)->first();
    }

    protected function prepareTopicList()
    {
        /*
         * If channel exists, load the topics
         */
        if ($channel = $this->getChannel()) {

            $currentPage = post('page');
            $searchString = trim(post('search'));
            $topics = TopicModel::make()->listFrontEnd($currentPage, 'updated_at', $channel->id, $searchString);

            /*
             * Signed in member
             */
            $this->page['member'] = $this->member = MemberModel::getFromUser();
            if ($this->member) {
                $topics = TopicWatch::setFlagsOnTopics($topics, $this->member);
                ChannelWatch::flagAsWatched($channel, $this->member);
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

        /*
         * Page links
         */
        $this->topicPage = $this->page['topicPage'] = $this->property('topicPage');
        $this->topicPageIdParam = $this->page['topicPageIdParam'] = $this->property('topicPageIdParam');
        $this->memberPage = $this->page['memberPage'] = $this->property('memberPage');
        $this->memberPageIdParam = $this->page['memberPageIdParam'] = $this->property('memberPageIdParam');

        $this->page['isGuest'] = !Auth::check();
    }

}
<?php namespace RainLab\Forum\Components;

use Auth;
use Request;
use Redirect;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\TopicWatch;
use RainLab\Forum\Models\Topic as TopicModel;
use RainLab\Forum\Models\Channel as ChannelModel;
use RainLab\Forum\Models\Member as MemberModel;

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
            'name'        => 'Channel',
            'description' => 'Displays a list of posts belonging to a channel.'
        ];
    }

    public function defineProperties()
    {
        return [
            'idParam' => [
                'title'       => 'Slug param name',
                'description' => 'The URL route parameter used for looking up the channel by its slug. A hard coded slug can also be used.',
                'default'     => ':slug',
                'type'        => 'string'
            ],
            'memberPage' => [
                'title'       => 'Member Page',
                'description' => 'Page name to use for clicking on a member.',
                'type'        => 'dropdown'
            ],
            'memberPageIdParam' => [
                'title'       => 'Member page param name',
                'description' => 'The expected parameter name used when creating links to the member page.',
                'type'        => 'string',
                'default'     => ':slug',
            ],
            'topicPage' => [
                'title'       => 'Topic Page',
                'description' => 'Page name to use for clicking on a conversation topic.',
                'type'        => 'dropdown'
            ],
            'topicPageIdParam' => [
                'title'       => 'Topic page param name',
                'description' => 'The expected parameter name used when creating links to the topic page.',
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
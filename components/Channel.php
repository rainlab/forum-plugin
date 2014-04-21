<?php namespace RainLab\Forum\Components;

use Request;
use Redirect;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Topic as TopicModel;
use RainLab\Forum\Models\Channel as ChannelModel;

class Channel extends ComponentBase
{

    private $channel = null;
    
    /**
     * @var Collection Topics cache for Twig access.
     */
    public $topics = null;

    const PARAM_SLUG = 'slug';

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
            'memberPage' => [
                'title'       => 'Member Page',
                'description' => 'Page name to use for clicking on a member.',
                'type'        => 'string' // @todo Page picker
            ],
            'topicPage' => [
                'title'       => 'Topic Page',
                'description' => 'Page name to use for clicking on a conversation topic.',
                'type'        => 'string' // @todo Page picker
            ],
        ];
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

        if (!$slug = $this->param(static::PARAM_SLUG))
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
            $this->page['topics'] = $this->topics = $topics;

            /*
             * Pagination
             */
            $queryArr = [];
            if ($searchString) $queryArr['search'] = $searchString;
            $queryArr['page'] = '';
            $paginationUrl = Request::url() . '?' . http_build_query($queryArr);

            if ($currentPage > ($lastPage = $topics->getLastPage()) && $currentPage > 1)
                return Redirect::to($paginationUrl . $lastPage);

            $this->page['paginationUrl'] = $paginationUrl;
        }

        /*
         * Load the page links
         */
        $links = [
            'member' => $this->property('memberPage'),
            'topic' => $this->property('topicPage'),
        ];

        $this->page['forumLink'] = $links;
    }

}
<?php namespace RainLab\Forum\Components;

use Lang;
use Response;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Topic as ForumTopic;
use RainLab\Forum\Models\Channel as ForumChannel;

class RssFeed extends ComponentBase
{
    /**
     * A collection of topics to display
     * @var Collection
     */
    public $topics;

    /**
     * If the post list should be filtered by a channel, the model to use.
     * @var Model
     */
    public $channel;

    /**
     * Reference to the page name for the main blog page.
     * @var string
     */
    public $forumPage;

    /**
     * Reference to the page name for linking to topics.
     * @var string
     */
    public $topicPage;

    public function componentDetails()
    {
        return [
            'name'        => 'rainlab.forum::lang.settings.rssfeed_title',
            'description' => 'rainlab.forum::lang.settings.rssfeed_description'
        ];
    }

    public function defineProperties()
    {
        return [
            'channelFilter' => [
                'title'       => 'rainlab.forum::lang.settings.channels_filter',
                'description' => 'rainlab.forum::lang.settings.channels_filter_description',
                'type'        => 'string',
                'default'     => '',
            ],
            'topicsPerPage' => [
                'title'             => 'rainlab.forum::lang.topics.per_page',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'rainlab.forum::lang.topics.per_page_validation',
                'default'           => '20',
            ],
            'forumPage' => [
                'title'       => 'rainlab.forum::lang.settings.rssfeed_forum',
                'description' => 'rainlab.forum::lang.settings.rssfeed_forum_description',
                'type'        => 'dropdown',
                'default'     => 'blog/post',
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
        $this->prepareVars();

        $xmlFeed = $this->renderPartial('@default');

        return Response::make($xmlFeed, '200')->header('Content-Type', 'text/xml');
    }

    protected function prepareVars()
    {
        $this->forumPage = $this->page['forumPage'] = $this->property('forumPage');
        $this->topicPage = $this->page['topicPage'] = $this->property('topicPage');
        $this->channel = $this->page['channel'] = $this->loadChannel();
        $this->topics = $this->page['topics'] = $this->listTopics();

        $this->page['link'] = $this->pageUrl($this->forumPage);
        $this->page['rssLink'] = $this->currentPageUrl();
    }

    protected function listTopics()
    {
        $channel = $this->channel ? $this->channel->id : null;

        /*
         * List all the topics, eager load their categories
         */
        $topics = ForumTopic::with('channel')->listFrontEnd([
            'perPage'  => $this->property('topicsPerPage'),
            'channels' => $channel,
            'sticky'   => false,
        ]);

        /*
         * Add a "url" helper attribute for linking to each post and channel
         */
        $topics->each(function($post) {
            $post->setUrl($this->topicPage, $this->controller);
        });

        return $topics;
    }

    protected function loadChannel()
    {
        if (!$channelId = $this->property('channelFilter')) {
            return null;
        }

        if (!$channel = ForumChannel::whereSlug($channelId)->first()) {
            return null;
        }

        return $channel;
    }
}

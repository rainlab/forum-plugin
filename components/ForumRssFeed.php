<?php namespace RainLab\Forum\Components;

use Response;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Topic as ForumTopic;
use RainLab\Forum\Models\Channel as ForumChannel;

/**
 * ForumRssFeed component for displaying an RSS feed of conversations
 */
class ForumRssFeed extends ComponentBase
{
    /**
     * @var Collection topics to display
     */
    public $topics;

    /**
     * @var Model channel to use, if the post list should be filtered by a channel.
     */
    public $channel;

    /**
     * @var string forumPage reference to the page name for the main blog page.
     */
    public $forumPage;

    /**
     * @var string topicPage reference to the page name for linking to topics.
     */
    public $topicPage;

    /**
     * componentDetails
     */
    public function componentDetails()
    {
        return [
            'name' => 'rainlab.forum::lang.settings.rssfeed_title',
            'description' => 'rainlab.forum::lang.settings.rssfeed_description'
        ];
    }

    /**
     * defineProperties
     */
    public function defineProperties()
    {
        return [
            'channelFilter' => [
                'title' => 'rainlab.forum::lang.settings.channels_filter',
                'description' => 'rainlab.forum::lang.settings.channels_filter_description',
                'type' => 'string',
                'default' => '',
            ],
            'topicsPerPage' => [
                'title' => 'rainlab.forum::lang.topics.per_page',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'rainlab.forum::lang.topics.per_page_validation',
                'default' => '20',
            ],
            'forumPage' => [
                'title' => 'rainlab.forum::lang.settings.rssfeed_forum',
                'description' => 'rainlab.forum::lang.settings.rssfeed_forum_description',
                'type' => 'dropdown',
                'default' => 'blog/post',
            ],
            'topicPage' => [
                'title' => 'rainlab.forum::lang.topic.page_name',
                'description' => 'rainlab.forum::lang.topic.page_help',
                'type' => 'dropdown',
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
        $this->prepareVars();

        $xmlFeed = $this->renderPartial('@default');

        return Response::make($xmlFeed, '200')->header('Content-Type', 'text/xml');
    }

    /**
     * prepareVars
     */
    protected function prepareVars()
    {
        $this->forumPage = $this->page['forumPage'] = $this->property('forumPage');
        $this->topicPage = $this->page['topicPage'] = $this->property('topicPage');
        $this->channel = $this->page['channel'] = $this->loadChannel();
        $this->topics = $this->page['topics'] = $this->listTopics();

        $this->page['link'] = $this->pageUrl($this->forumPage);
        $this->page['rssLink'] = $this->currentPageUrl();
    }

    /**
     * listTopics
     */
    protected function listTopics()
    {
        $channel = $this->channel ? $this->channel->id : null;

        // List all the topics, eager load their categories
        $topics = ForumTopic::with('channel')->listFrontEnd([
            'perPage'  => $this->property('topicsPerPage'),
            'channels' => $channel,
            'sticky'   => false,
        ]);

        // Add a "url" helper attribute for linking to each post and channel
        $topics->each(function($post) {
            $post->setUrl($this->topicPage, $this->controller);
        });

        return $topics;
    }

    /**
     * loadChannel
     */
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

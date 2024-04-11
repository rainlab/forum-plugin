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
            'name' => "RSS Feed",
            'description' => "Generates an RSS feed containing topics from the forum."
        ];
    }

    /**
     * defineProperties
     */
    public function defineProperties()
    {
        return [
            'channelFilter' => [
                'title' => "Channel filter",
                'description' => "Enter a category slug or URL parameter to filter the topics by. Leave empty to show all topics.",
                'type' => 'string',
                'default' => '',
            ],
            'topicsPerPage' => [
                'title' => "Topics per page",
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => "Invalid format of the topics per page value",
                'default' => '20',
            ],
            'forumPage' => [
                'title' => "Forum page",
                'description' => "Name of the main forum page file for generating links. This property is used by the default component partial.",
                'type' => 'dropdown',
                'default' => 'blog/post',
            ],
            'topicPage' => [
                'title' => "Topic Page",
                'description' => "Page name to use for clicking on a conversation topic.",
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

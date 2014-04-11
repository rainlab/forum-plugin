<?php namespace RainLab\Forum\Components;

use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Topic;
use RainLab\Forum\Models\Channel as ChannelModel;

class Channel extends ComponentBase
{

    private $topics = null;
    private $channel = null;

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
        $this->page['channel'] = $this->getChannel();
        $this->page['topics'] = $this->listTopics();
        $this->prepareTopicList();
    }

    public function getChannel()
    {
        if ($this->channel !== null)
            return $this->channel;

        if (!$slug = $this->param(static::PARAM_SLUG))
            return null;

        return $this->channel = ChannelModel::whereSlug($slug)->first();
    }

    public function listTopics()
    {
        if ($this->topics !== null)
            return $this->topics;

        if (!$channel = $this->getChannel())
            return null;

        $channelIds = $channel->children()->lists('id');

        return $this->topics = Topic::make()->listFrontEnd();
    }

    protected function prepareTopicList()
    {
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
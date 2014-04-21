<?php namespace RainLab\Forum\Components;

use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Channel;

class ChannelList extends ComponentBase
{

    private $channels = null;

    public function componentDetails()
    {
        return [
            'name'        => 'Channel List',
            'description' => 'Displays a list of all visible channels.'
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
            'channelPage' => [
                'title'       => 'Channel Page',
                'description' => 'Page name to use for clicking on a channel.',
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

        $this->page['channels'] = $this->listChannels();
        $this->prepareChannelList();
    }

    public function listChannels()
    {
        if ($this->channels !== null)
            return $this->channels;

        return $this->channels = Channel::make()->getEagerRoot();
    }

    protected function prepareChannelList()
    {
        /*
         * Load the page links
         */
        $links = [
            'member' => $this->property('memberPage'),
            'channel' => $this->property('channelPage'),
            'topic' => $this->property('topicPage'),
        ];

        $this->page['forumLink'] = $links;
    }

}
<?php namespace RainLab\Forum\Components;

use Cms\Classes\ComponentBase;

class Member extends ComponentBase
{

    const PARAM_USERNAME = 'username';

    public function componentDetails()
    {
        return [
            'name'        => 'Member',
            'description' => 'Displays form member information and activity.'
        ];
    }

    public function defineProperties()
    {
        return [
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

        // $this->page['channels'] = $this->listChannels();
        $this->prepareChannelList();
    }

    protected function prepareChannelList()
    {
        /*
         * Load the page links
         */
        $links = [
            'channel' => $this->property('channelPage'),
            'topic' => $this->property('topicPage'),
        ];

        $this->page['forumLink'] = $links;
    }

}
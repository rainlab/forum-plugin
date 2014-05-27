<?php namespace RainLab\Forum\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Channel;

class Channels extends ComponentBase
{

    private $channels = null;

    public $memberPage;
    public $memberPageParamId;
    public $topicPage;
    public $topicPageParamId;
    public $channelPage;
    public $channelPageParamId;

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
                'type'        => 'dropdown',
            ],
            'memberPageParamId' => [
                'title'       => 'Member page param name',
                'description' => 'The expected parameter name used when creating links to the member page.',
                'type'        => 'string',
                'default'     => ':slug',
            ],
            'channelPage' => [
                'title'       => 'Channel Page',
                'description' => 'Page name to use for clicking on a channel.',
                'type'        => 'dropdown',
            ],
            'channelPageParamId' => [
                'title'       => 'Channel page param name',
                'description' => 'The expected parameter name used when creating links to the channel page.',
                'type'        => 'string',
                'default'     => ':slug',
            ],
            'topicPage' => [
                'title'       => 'Topic Page',
                'description' => 'Page name to use for clicking on a conversation topic.',
                'type'        => 'dropdown',
            ],
            'topicPageParamId' => [
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

        $this->page['channels'] = $this->listChannels();
        $this->prepareVars();
    }

    public function listChannels()
    {
        if ($this->channels !== null)
            return $this->channels;

        return $this->channels = Channel::make()->getEagerRoot();
    }

    protected function prepareVars()
    {
        /*
         * Page links
         */
        $this->memberPage = $this->page['memberPage'] = $this->property('memberPage');
        $this->memberPageParamId = $this->page['memberPageParamId'] = $this->property('memberPageParamId');
        $this->channelPage = $this->page['channelPage'] = $this->property('channelPage');
        $this->channelPageParamId = $this->page['channelPageParamId'] = $this->property('channelPageParamId');
        $this->topicPage = $this->page['topicPage'] = $this->property('topicPage');
        $this->topicPageParamId = $this->page['topicPageParamId'] = $this->property('topicPageParamId');
    }
}
<?php namespace RainLab\Forum\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Channel;
use RainLab\Forum\Models\ChannelWatch;
use RainLab\Forum\Models\Member as MemberModel;

class Channels extends ComponentBase
{

    private $member;
    private $channels;

    public $memberPage;
    public $memberPageIdParam;
    public $topicPage;
    public $topicPageIdParam;
    public $channelPage;
    public $channelPageIdParam;

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
                'title'       => 'rainlab.forum::lang.member.page_name',
                'description' => 'rainlab.forum::lang.member.page_help',
                'type'        => 'dropdown',
            ],
            'memberPageIdParam' => [
                'title'       => 'rainlab.forum::lang.member.param_name',
                'description' => 'rainlab.forum::lang.member.param_help',
                'type'        => 'string',
                'default'     => ':slug',
            ],
            'channelPage' => [
                'title'       => 'Channel Page',
                'description' => 'Page name to use for clicking on a channel.',
                'type'        => 'dropdown',
            ],
            'channelPageIdParam' => [
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

        $this->page['channels'] = $this->listChannels();
        $this->prepareVars();
    }

    public function listChannels()
    {
        if ($this->channels !== null)
            return $this->channels;

        $channels = Channel::isVisible()->get();

        $this->page['member'] = $this->member = MemberModel::getFromUser();
        if ($this->member)
            $channels = ChannelWatch::setFlagsOnChannels($channels, $this->member);

        $channels = $channels->toNested();

        return $this->channels = $channels;
    }

    protected function prepareVars()
    {
        /*
         * Page links
         */
        $this->memberPage = $this->page['memberPage'] = $this->property('memberPage');
        $this->memberPageIdParam = $this->page['memberPageIdParam'] = $this->property('memberPageIdParam');
        $this->channelPage = $this->page['channelPage'] = $this->property('channelPage');
        $this->channelPageIdParam = $this->page['channelPageIdParam'] = $this->property('channelPageIdParam');
        $this->topicPage = $this->page['topicPage'] = $this->property('topicPage');
        $this->topicPageIdParam = $this->page['topicPageIdParam'] = $this->property('topicPageIdParam');
    }

}
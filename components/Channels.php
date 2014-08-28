<?php namespace RainLab\Forum\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Channel;
use RainLab\Forum\Models\ChannelWatch;
use RainLab\Forum\Models\Member as MemberModel;

class Channels extends ComponentBase
{

    /**
     * @var RainLab\Forum\Models\Member Member cache
     */
    private $member;

    /**
     * @var RainLab\Forum\Models\Channel Channel collection cache
     */
    private $channels;

    /**
     * @var string Reference to the page name for linking to members.
     */
    public $memberPage;

    /**
     * @var string Reference to the page name for linking to topics.
     */
    public $topicPage;

    /**
     * @var string Reference to the page name for linking to channels.
     */
    public $channelPage;

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
            'channelPage' => [
                'title'       => 'Channel Page',
                'description' => 'Page name to use for clicking on a channel.',
                'type'        => 'dropdown',
            ],
            'topicPage' => [
                'title'       => 'Topic Page',
                'description' => 'Page name to use for clicking on a conversation topic.',
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
        $this->addCss('/plugins/rainlab/forum/assets/css/forum.css');

        $this->prepareVars();
        $this->page['channels'] = $this->listChannels();
    }

    protected function prepareVars()
    {
        /*
         * Page links
         */
        $this->memberPage = $this->page['memberPage'] = $this->property('memberPage');
        $this->channelPage = $this->page['channelPage'] = $this->property('channelPage');
        $this->topicPage = $this->page['topicPage'] = $this->property('topicPage');
    }

    public function listChannels()
    {
        if ($this->channels !== null)
            return $this->channels;

        $channels = Channel::with('first_topic')->isVisible()->get();

        /*
         * Add a "url" helper attribute for linking to each channel
         */
        $channels->each(function($channel){
            $channel->setUrl($this->channelPage, $this->controller);

            if ($channel->first_topic)
                $channel->first_topic->setUrl($this->topicPage, $this->controller);
        });

        $this->page['member'] = $this->member = MemberModel::getFromUser();
        if ($this->member)
            $channels = ChannelWatch::setFlagsOnChannels($channels, $this->member);

        $channels = $channels->toNested();

        return $this->channels = $channels;
    }

}
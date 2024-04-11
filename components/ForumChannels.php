<?php namespace RainLab\Forum\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Channel;
use RainLab\Forum\Models\Member as MemberModel;
use RainLab\Forum\Classes\TopicTracker;

/**
 * ForumChannels component displays a list of channels
 */
class ForumChannels extends ComponentBase
{
    /**
     * @var RainLab\Forum\Models\Member member cache
     */
    protected $member;

    /**
     * @var RainLab\Forum\Models\Channel channels collection cache
     */
    protected $channels;

    /**
     * @var string memberPage reference to the page name for linking to members.
     */
    public $memberPage;

    /**
     * @var string topicPage reference to the page name for linking to topics.
     */
    public $topicPage;

    /**
     * @var string channelPage reference to the page name for linking to channels.
     */
    public $channelPage;

    /**
     * componentDetails
     */
    public function componentDetails()
    {
        return [
            'name' => 'rainlab.forum::lang.channels.list_name',
            'description' => 'rainlab.forum::lang.channels.list_desc'
        ];
    }

    public function defineProperties()
    {
        return [
            'memberPage' => [
                'title' => 'rainlab.forum::lang.member.page_name',
                'description' => 'rainlab.forum::lang.member.page_help',
                'type' => 'dropdown',
            ],
            'channelPage' => [
                'title' => 'rainlab.forum::lang.channel.page_name',
                'description' => 'rainlab.forum::lang.channel.page_help',
                'type' => 'dropdown',
            ],
            'topicPage' => [
                'title' => 'rainlab.forum::lang.topic.page_name',
                'description' => 'rainlab.forum::lang.topic.page_help',
                'type' => 'dropdown',
            ],
            'includeStyles' => [
                'title' => 'rainlab.forum::lang.components.general.properties.includeStyles',
                'description' => 'rainlab.forum::lang.components.general.properties.includeStyles_desc',
                'type' => 'checkbox',
                'default' => true
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
        if ($this->property('includeStyles', true)) {
            $this->addCss('assets/css/forum.css');
        }

        $this->prepareVars();
        $this->page['channels'] = $this->listChannels();
    }

    /**
     * prepareVars
     */
    protected function prepareVars()
    {
        $this->memberPage = $this->page['memberPage'] = $this->property('memberPage');
        $this->channelPage = $this->page['channelPage'] = $this->property('channelPage');
        $this->topicPage = $this->page['topicPage'] = $this->property('topicPage');
    }

    /**
     * listChannels
     */
    public function listChannels()
    {
        if ($this->channels !== null) {
            return $this->channels;
        }

        $channels = Channel::with('first_topic')->isVisible()->get();

        // Add a "url" helper attribute for linking to each channel
        $channels->each(function($channel) {
            $channel->setUrl($this->channelPage, $this->controller);

            if ($channel->first_topic) {
                $channel->first_topic->setUrl($this->topicPage, $this->controller);
            }
        });

        $this->page['member'] = $this->member = MemberModel::getFromUser();

        if ($this->member) {
            $channels = TopicTracker::instance()->setFlagsOnChannels($channels, $this->member);
        }

        $channels = $channels->toNested();

        return $this->channels = $channels;
    }
}

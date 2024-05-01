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
            'name' => "Channel List",
            'description' => "Displays a list of all visible channels."
        ];
    }

    public function defineProperties()
    {
        return [
            'memberPage' => [
                'title' => "Member Page",
                'description' => "Page name to use for clicking on a Member.",
                'type' => 'dropdown',
            ],
            'channelPage' => [
                'title' => "Channel Page",
                'description' => "Page name to use for clicking on a Channel.",
                'type' => 'dropdown',
            ],
            'topicPage' => [
                'title' => "Topic Page",
                'description' => "Page name to use for clicking on a conversation topic.",
                'type' => 'dropdown',
            ],
            'includeStyles' => [
                'title' => "Enable CSS",
                'description' => "Include the CSS files with default styles for the forum",
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

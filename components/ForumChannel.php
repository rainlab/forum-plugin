<?php namespace RainLab\Forum\Components;

use Auth;
use Request;
use Redirect;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Topic as TopicModel;
use RainLab\Forum\Models\Channel as ChannelModel;
use RainLab\Forum\Models\Member as MemberModel;
use RainLab\Forum\Classes\TopicTracker;

/**
 * ForumChannel component displays a list of posts belonging to a channel.
 */
class ForumChannel extends ComponentBase
{
    /**
     * @var boolean embedMode determines if this component is being used by the EmbedChannel component.
     */
    public $embedMode = false;

    /**
     * @var string embedTopicParam if this channel is embedded, pass the topic slug to this route parameter for linking to topics.
     */
    public $embedTopicParam = 'topicSlug';

    /**
     * @var RainLab\Forum\Models\Member member cache
     */
    protected $member = null;

    /**
     * @var RainLab\Forum\Models\Channel channel cache
     */
    protected $channel = null;

    /**
     * @var string memberPage reference to the page name for linking to members.
     */
    public $memberPage;

    /**
     * @var string topicPage reference to the page name for linking to topics.
     */
    public $topicPage;

    /**
     * @var Collection topics cache for Twig access.
     */
    public $topics = null;

    /**
     * componentDetails
     */
    public function componentDetails()
    {
        return [
            'name' => "Channel",
            'description' => "Displays a list of posts belonging to a channel.",
        ];
    }

    /**
     * defineProperties
     */
    public function defineProperties()
    {
        return [
            'slug' => [
                'title' => "Slug param name",
                'description' => "The URL route parameter used for looking up the channel by its slug. A hard coded slug can also be used.",
                'default' => '{{ :slug }}',
                'type' => 'string',
            ],
            'memberPage' => [
                'title' => "Member Page",
                'description' => "Page name to use for clicking on a Member.",
                'type' => 'dropdown',
                'group' => 'Links',
            ],
            'topicPage' => [
                'title' => "Topic Page",
                'description' => "Page name to use for clicking on a conversation topic.",
                'type' => 'dropdown',
                'group' => 'Links',
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
        $this->page['channel'] = $this->getChannel();

        return $this->prepareTopicList();
    }

    /**
     * prepareVars
     */
    protected function prepareVars()
    {
        $this->topicPage = $this->page['topicPage'] = $this->property('topicPage');
        $this->memberPage = $this->page['memberPage'] = $this->property('memberPage');
    }

    /**
     * getChannel
     */
    public function getChannel()
    {
        if ($this->channel !== null) {
            return $this->channel;
        }

        if (!$slug = $this->property('slug')) {
            return null;
        }

        return $this->channel = ChannelModel::whereSlug($slug)->first();
    }

    /**
     * prepareTopicList
     */
    protected function prepareTopicList()
    {
        // If channel exists, load the topics
        if ($channel = $this->getChannel()) {
            $currentPage = input('page');
            $searchString = trim(input('search'));
            $topics = TopicModel::with('last_post_member')->listFrontEnd([
                'page' => $currentPage,
                'sort' => 'updated_at',
                'channels' => $channel->id,
                'search' => $searchString,
            ]);

            // Add a "url" helper attribute for linking to each topic
            $topics->each(function($topic) {
                if ($this->embedMode) {
                    $topic->url = $this->pageUrl($this->topicPage, [$this->embedTopicParam => $topic->slug]);
                }
                else {
                    $topic->setUrl($this->topicPage, $this->controller);
                }

                if ($topic->last_post_member) {
                    $topic->last_post_member->setUrl($this->memberPage, $this->controller);
                }

                if ($topic->start_member) {
                    $topic->start_member->setUrl($this->memberPage, $this->controller);
                }
            });

            // Signed in member
            $this->page['member'] = $this->member = MemberModel::getFromUser();

            if ($this->member) {
                $this->member->setUrl($this->memberPage, $this->controller);
                $topics = TopicTracker::instance()->setFlagsOnTopics($topics, $this->member);
            }

            $this->page['topics'] = $this->topics = $topics;

            // Pagination
            if ($topics) {
                $queryArr = [];
                if ($searchString) {
                    $queryArr['search'] = $searchString;
                }
                $queryArr['page'] = '';
                $paginationUrl = Request::url() . '?' . http_build_query($queryArr);

                if ($currentPage > ($lastPage = $topics->lastPage()) && $currentPage > 1) {
                    return Redirect::to($paginationUrl . $lastPage);
                }

                $this->page['paginationUrl'] = $paginationUrl;
            }
        }

        $this->page['isGuest'] = !Auth::check();
    }
}

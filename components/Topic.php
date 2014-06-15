<?php namespace RainLab\Forum\Components;

use Auth;
use Flash;
use Request;
use Redirect;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use System\Classes\ApplicationException;
use RainLab\Forum\Models\Topic as TopicModel;
use RainLab\Forum\Models\Channel as ChannelModel;
use RainLab\Forum\Models\Member as MemberModel;
use RainLab\Forum\Models\Post as PostModel;
use RainLab\Forum\Models\TopicWatch;

class Topic extends ComponentBase
{
    /**
     * @var boolean Determine if this component is being used by the EmbedChannel component.
     */
    public $embedMode = false;

    private $topic = null;
    private $channel = null;
    private $member = null;

    public $memberPage;
    public $memberPageIdParam;
    public $channelPage;
    public $channelPageIdParam;
    public $returnUrl;

    /**
     * @var Collection Posts cache for Twig access.
     */
    public $posts = null;

    public function componentDetails()
    {
        return [
            'name'        => 'Topic',
            'description' => 'Displays a topic and posts.'
        ];
    }

    public function defineProperties()
    {
        return [
            'idParam' => [
                'title'       => 'Slug param name',
                'description' => 'The URL route parameter used for looking up the topic by its slug. A hard coded slug can also be used.',
                'default'     => ':slug',
                'type'        => 'string'
            ],
            'memberPage' => [
                'title'       => 'Member Page',
                'description' => 'Page name to use for clicking on a member.',
                'type'        => 'dropdown',
            ],
            'memberPageIdParam' => [
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
            'channelPageIdParam' => [
                'title'       => 'Channel page param name',
                'description' => 'The expected parameter name used when creating links to the channel page.',
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

        $this->page['channel'] = $this->getChannel();
        $this->page['topic'] = $topic = $this->getTopic();
        return $this->preparePostList();
    }

    public function getTopic()
    {
        if ($this->topic !== null)
            return $this->topic;

        if (!$slug = $this->propertyOrParam('idParam'))
            return null;

        $topic = TopicModel::whereSlug($slug)->first();

        if ($topic)
            $topic->increaseViewCount();

        return $this->topic = $topic;
    }

    public function getChannel()
    {
        if ($this->channel !== null)
            return $this->channel;

        if ($topic = $this->getTopic())
            $channel = $topic->channel;

        elseif ($channelId = post('channel'))
            $channel = ChannelModel::find($channelId);

        else
            $channel = null;

        return $this->channel = $channel;
    }

    public function getChannelList()
    {
        return ChannelModel::make()->getRootList('title', 'id');
    }

    protected function preparePostList()
    {
        /*
         * If topic exists, loads the posts
         */
        if ($topic = $this->getTopic()) {

            $currentPage = post('page');
            $searchString = trim(post('search'));
            $posts = PostModel::make()->listFrontEnd($currentPage, 'created_at', $topic->id, $searchString);
            $this->page['posts'] = $this->posts = $posts;

            /*
             * Pagination
             */
            $queryArr = [];
            if ($searchString) $queryArr['search'] = $searchString;
            $queryArr['page'] = '';
            $paginationUrl = Request::url() . '?' . http_build_query($queryArr);

            $lastPage = $posts->getLastPage();
            if ($currentPage == 'last' || $currentPage > $lastPage && $currentPage > 1)
                return Redirect::to($paginationUrl . $lastPage);

            $this->page['paginationUrl'] = $paginationUrl;
        }

        /*
         * Signed in member
         */
        $this->page['member'] = $this->member = MemberModel::getFromUser();
        if ($this->topic && $this->member)
            TopicWatch::flagAsWatched($this->topic, $this->member);

        /*
         * Return URL
         */
        if ($this->getChannel()) {
            if ($this->embedMode == 'single')
                $returnUrl = null;
            elseif ($this->embedMode)
                $returnUrl = $this->currentPageUrl([$this->property('idParam') => null]);
            else
                $returnUrl = $this->pageUrl($this->channelPage, [$this->channelPageIdParam => $this->channel->slug]);

             $this->returnUrl = $this->page['returnUrl'] = $returnUrl;
         }

        /*
         * Page links
         */
        $this->memberPage = $this->page['memberPage'] = $this->property('memberPage');
        $this->memberPageIdParam = $this->page['memberPageIdParam'] = $this->property('memberPageIdParam');
        $this->channelPage = $this->page['channelPage'] = $this->property('channelPage');
        $this->channelPageIdParam = $this->page['channelPageIdParam'] = $this->property('channelPageIdParam');
    }

    public function onCreate()
    {
        try {
            if (!$user = Auth::getUser())
                throw new ApplicationException('You should be logged in.');

            $member = MemberModel::getFromUser($user);
            $channel = $this->getChannel();

            $topic = TopicModel::createInChannel($channel, $member, post());

            Flash::success(post('flash', 'Topic created successfully!'));

            /*
             * Redirect to the intended page after successful update
             */
            $redirectUrl = post('redirect', $this->currentPageUrl([
                $this->property('idParam') => $topic->slug
            ]));

            return Redirect::to($redirectUrl);
        }
        catch (\Exception $ex) {
            Flash::error($ex->getMessage());
        }
    }

    public function onPost()
    {
        try {
            if (!$user = Auth::getUser())
                throw new ApplicationException('You should be logged in.');

            $member = MemberModel::getFromUser($user);
            $topic = $this->getTopic();

            $post = PostModel::createInTopic($topic, $member, post());

            Flash::success(post('flash', 'Response added successfully!'));

            /*
             * Redirect to the intended page after successful update
             */
            $redirectUrl = post('redirect', $this->currentPageUrl([
                $this->property('idParam') => $topic->slug
            ]));

            return Redirect::to($redirectUrl.'?page=last');
        }
        catch (\Exception $ex) {
            Flash::error($ex->getMessage());
        }
    }

    public function onUpdate()
    {
        $post = PostModel::find(post('post'));

        if (!$post->canEdit())
            throw new ApplicationException('Permission denied.');

        $mode = post('mode', 'edit');
        if ($mode == 'save') {
            $post->save(post());
        }
        elseif ($mode == 'delete') {
            $post->delete();
        }
        elseif ($mode == 'view') {
            // Do nothing
        }

        $this->page['mode'] = $mode;
        $this->page['post'] = $post;
    }

    public function onMoveTopic()
    {
        $member = MemberModel::getFromUser();
        if (!$member->is_moderator) {
            Flash::error('Access denied');
            return;
        }

        $channelId = post('channel');
        $channel = ChannelModel::find($channelId);
        if ($channel) {
            $this->getTopic()->moveToChannel($channel);
            Flash::success(post('flash', 'Post moved successfully!'));
        }
        else {
            Flash::error('Unable to find a channel to move to.');
        }
    }
}
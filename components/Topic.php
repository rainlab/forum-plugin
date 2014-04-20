<?php namespace RainLab\Forum\Components;

use Auth;
use Flash;
use Request;
use Redirect;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Topic as TopicModel;
use RainLab\Forum\Models\Channel as ChannelModel;
use RainLab\Forum\Models\Member as MemberModel;
use RainLab\Forum\Models\Post as PostModel;

class Topic extends ComponentBase
{

    private $topic = null;
    private $channel = null;

    /**
     * @var Collection Posts cache for Twig access.
     */
    public $posts = null;

    const PARAM_SLUG = 'slug';

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
        ];
    }

    public function onRun()
    {
        $this->page['channel'] = $this->getChannel();
        $this->page['topic'] = $topic = $this->getTopic();
        $this->preparePostList();
    }

    public function getTopic()
    {
        if ($this->topic !== null)
            return $this->topic;

        if (!$slug = $this->param(static::PARAM_SLUG))
            return null;

        $topic = TopicModel::whereSlug($slug)->first();

        if ($topic) $topic->increment('count_views');

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

    protected function preparePostList()
    {
        /*
         * If topic exists, loads the posts
         */
        if ($topic = $this->getTopic()) {

            $currentPage = post('page');
            $searchString = trim(post('search'));
            $posts = PostModel::make()->listFrontEnd($currentPage, 'created_at', $topic, $searchString);
            $this->page['posts'] = $this->posts = $posts;

            /*
             * Pagination
             */
            $queryArr = [];
            if ($searchString) $queryArr['search'] = $searchString;
            $queryArr['page'] = '';
            $paginationUrl = Request::url() . '?' . http_build_query($queryArr);

            if ($currentPage > ($lastPage = $posts->getLastPage()) && $currentPage > 1)
                return Redirect::to($paginationUrl . $lastPage);

            $this->page['paginationUrl'] = $paginationUrl;
        }

        /*
         * Load the page links
         */
        $links = [
            'member' => $this->property('memberPage'),
            'channel' => $this->property('channelPage'),
        ];

        $this->page['forumLink'] = $links;
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
                'slug' => $topic->slug
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
        }
        catch (\Exception $ex) {
            Flash::error($ex->getMessage());
        }
    }

}
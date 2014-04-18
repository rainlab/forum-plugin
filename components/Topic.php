<?php namespace RainLab\Forum\Components;

use DB as Db;
use Flash;
use Redirect;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Topic as TopicModel;
use RainLab\Forum\Models\Channel as ChannelModel;
use RainLab\Forum\Models\Post;

class Topic extends ComponentBase
{

    private $topic = null;
    private $channel = null;

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

        $topic->increment('count_views');

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
         * If topic exists, prepare the posts
         */
        if ($topic = $this->getTopic()) {
            $this->page['posts'] = $topic->posts()->paginate(20);
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
            $topic = new TopicModel;
            $topic->subject = post('subject');
            $topic->channel = $this->getChannel();

            $post = new Post;
            $post->topic = $topic;
            $post->content = post('content');

            Db::transaction(function() use ($topic, $post) {
                $topic->save();
                $post->save();
            });

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
            $topic = $this->getTopic();

            $post = new Post;
            $post->topic = $topic;
            $post->content = post('content');
            $post->save();

        }
        catch (\Exception $ex) {
            Flash::error($ex->getMessage());
        }
    }

}
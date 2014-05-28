<?php namespace RainLab\Forum\Models;

use App;
use Model;
use DB as Db;
use System\Classes\ApplicationException;

/**
 * Topic Model
 */
class Topic extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'rainlab_forum_topics';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['subject'];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'subject' => 'required',
        'channel_id' => 'required',
        'start_member_id' => 'required'
    ];

    /**
     * @var array Date fields
     */
    public $dates = ['last_post_at'];

    /**
     * @var array Auto generated slug
     */
    public $slugs = ['slug' => 'subject'];

    /**
     * @var array Relations
     */
    public $hasMany = [
        'posts' => ['RainLab\Forum\Models\Post']
    ];

    public $belongsTo = [
        'channel' => ['RainLab\Forum\Models\Channel'],
        'start_member' => ['RainLab\Forum\Models\Member'],
        'last_post' => ['RainLab\Forum\Models\Post'],
        'last_post_member' => ['RainLab\Forum\Models\Member'],
    ];

    /**
     * @var boolean Topic has new posts for member, set by PostWatch model
     */
    public $hasNew = true;

    /**
     * Creates a topic and a post inside a channel
     * @param  Channel $channel
     * @param  Member $member
     * @param  array $data Topic and post data: subject, content.
     * @return self
     */
    public static function createInChannel($channel, $member, $data)
    {
        $topic = new static;
        $topic->subject = array_get($data, 'subject');
        $topic->channel = $channel;
        $topic->start_member = $member;

        $post = new Post;
        $post->topic = $topic;
        $post->member = $member;
        $post->content = array_get($data, 'content');

        Db::transaction(function() use ($topic, $post) {
            $topic->save();
            $post->save();
        });

        return $topic;
    }

    /**
     * Auto creates a topic based on embed code and channel
     * @param  string $code        Embed code
     * @param  string $channelSlug Channel to create the topic in
     * @param  string $subject     Title for the topic (if created)
     * @return self
     */
    public static function createForEmbed($code, $channelSlug, $subject = null)
    {
        if (!$channel = Channel::whereSlug($channelSlug)->first())
            throw new ApplicationException('Unable to find a channel with slug: ' . $channelSlug);

        $topic = self::where('embed_code', $code)->where('channel_id', $channel->id)->first();

        if (!$topic) {
            $topic = new self;
            $topic->subject = $subject;
            $topic->embed_code = $code;
            $topic->channel = $channel;
            $topic->start_member_id = 0;
            $topic->save();
        }

        return $topic;
    }

    /**
     * Lists topics for the front end
     * @param  integer $page      Page number
     * @param  string  $sort      Sorting field
     * @param  Channel  $channels Topics in channels
     * @param  string  $search    Search query
     * @return self
     */
    public function listFrontEnd($page = 1, $sort = 'created_at', $channels = [], $search = '')
    {
        App::make('paginator')->setCurrentPage($page);
        $search = trim($search);

        $allowedSortingOptions = ['created_at', 'updated_at', 'subject'];
        if (!in_array($sort, $allowedSortingOptions))
            $sort = $allowedSortingOptions[0];

        $obj = $this->newQuery();
        $obj->orderBy('rainlab_forum_topics.' . $sort, in_array($sort, ['created_at', 'updated_at']) ? 'desc' : 'asc');

        if (strlen($search)) {
            $obj->joinWith('posts', false);
            $obj->searchWhere($search, ['rainlab_forum_topics.subject', 'rainlab_forum_posts.subject', 'content']);

            // Grouping due to the joinWith() call
            $obj->groupBy($this->getQualifiedKeyName());
        }

        if ($channels) {
            if (!is_array($channels))
                $channels = [$channels];

            $obj->whereIn('channel_id', $channels);
        }

        return $obj->paginate(20);
    }

    public function moveToChannel($channel)
    {
        $oldChannel = $this->channel;
        $this->timestamps = false;
        $this->channel = $channel;
        $this->save();
        $this->timestamps = true;
        $oldChannel->rebuildStats();
        $channel->rebuildStats();
    }

    public function increaseViewCount()
    {
        $this->timestamps = false;
        $this->increment('count_views');
        $this->timestamps = true;
    }

    public function afterCreate()
    {
        $this->start_member()->increment('count_topics');
        $this->channel()->increment('count_topics');
    }

    public function afterDelete()
    {
        $this->start_member()->decrement('count_topics');
        $this->channel()->decrement('count_topics');
        $this->channel()->decrement('count_posts', $this->posts()->count());
        $this->posts()->delete();
        TopicWatch::where('topic_id', $this->id)->delete();
    }

    public function canPost($member = null)
    {
        // @todo Add logic to check if topic is locked
        if (!$member)
            $member = Member::getFromUser();

        if ($member->is_banned)
            return false;

        return $member ? true : false;
    }
}
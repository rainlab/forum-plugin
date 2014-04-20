<?php namespace RainLab\Forum\Models;

use Model;
use Carbon\Carbon;

/**
 * Post Model
 */
class Post extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'rainlab_forum_posts';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['subject', 'content'];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'topic_id' => 'required',
        'member_id' => 'required',
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'topic' => ['RainLab\Forum\Models\Topic'],
        'member' => ['RainLab\Forum\Models\Member'],
    ];

    /**
     * Creates a postinside a topic
     * @param  Topic $topic
     * @param  Member $member
     * @param  array $data Post data: subject, content.
     * @return self
     */
    public static function createInTopic($topic, $member, $data)
    {
        $post = new static;
        $post->topic = $topic;
        $post->member = $member;
        $post->subject = array_get($data, 'subject', $topic->subject);
        $post->content = array_get($data, 'content');
        return $post->save();
    }

    public function afterCreate()
    {
        $this->member()->increment('count_posts');

        $this->topic->count_posts++;
        $this->topic->last_post_at = new Carbon;
        $this->topic->save();
        $this->topic->channel()->increment('count_posts');
    }

    public function afterDelete()
    {
        $this->member()->decrement('count_posts');

        $this->topic()->decrement('count_posts');
        $this->topic->channel()->decrement('count_posts');
    }

}
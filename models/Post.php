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
    public $rules = [];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'topic' => ['RainLab\Forum\Models\Topic'],
        'member' => ['RainLab\Forum\Models\Member'],
    ];

    public function afterCreate()
    {
        $this->topic->count_posts++;
        $this->topic->last_post_at = new Carbon;
        $this->topic->save();

        $this->topic->channel()->increment('count_posts');
    }

    public function afterDelete()
    {
        $this->topic()->decrement('count_posts');
        $this->topic->channel()->decrement('count_posts');
    }

}
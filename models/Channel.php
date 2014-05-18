<?php namespace RainLab\Forum\Models;

use Model;

/**
 * Channel Model
 */
class Channel extends Model
{

    private $firstTopic;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'rainlab_forum_channels';

    public $implement = ['October.Rain.Database.Behaviors.NestedSetModel'];

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['title', 'description', 'parent_id'];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'title' => 'required'
    ];

    /**
     * @var array Auto generated slug
     */
    public $slugs = ['slug' => 'title'];

    /**
     * @var array Relations
     */
    public $hasMany = [
        'topics' => ['RainLab\Forum\Models\Topic']
    ];

    /**
     * Returns the last updated topic in this channel.
     * @return Model
     */
    public function firstTopic()
    {
        if ($this->firstTopic !== null)
            return $this->firstTopic;

        return $this->firstTopic = $this->topics()->orderBy('updated_at', 'desc')->first();
    }

    /**
     * Rebuilds the statistics for the channel
     * @return void
     */
    public function rebuildStats()
    {
        $this->count_topics = $this->topics()->count();
        $this->count_posts = $this->topics()->sum('count_posts');
        $this->save();
    }

}
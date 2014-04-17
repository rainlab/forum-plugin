<?php namespace RainLab\Forum\Models;

use Model;

/**
 * Channel Model
 */
class Channel extends Model
{

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
    public $sluggable = ['slug' => 'title'];

    /**
     * @var array Relations
     */
    public $hasMany = [
        'topics' => ['RainLab\Forum\Models\Topic']
    ];


}
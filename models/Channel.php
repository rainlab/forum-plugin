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

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'title' => 'required'
    ];

    public $sluggable = ['slug' => 'title'];

    /**
     * @var array Relations
     */
    public $hasOne = [];

    public $hasMany = [
        'topics' => ['RainLab\Forum\Models\Topic']
    ];

    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}
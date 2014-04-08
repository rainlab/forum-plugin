<?php namespace RainLab\Forum\Models;

use Model;

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
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules
     */
    public $rules = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];

    public $hasMany = [
        'posts' => ['RainLab\Forum\Models\Post']
    ];

    public $belongsTo = [
        'channel' => ['RainLab\Forum\Models\Channel']
    ];

    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}
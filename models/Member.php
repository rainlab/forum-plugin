<?php namespace RainLab\Forum\Models;

use Model;

/**
 * Member Model
 */
class Member extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'rainlab_forum_members';

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
    public $belongsTo = [
        'user' => ['RainLab\User\Models\User']
    ];
}
<?php namespace RainLab\Forum\Models;

use Str;
use Auth;
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
     * @var array Auto generated slug
     */
    public $slugs = ['slug' => 'username'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user' => ['RainLab\User\Models\User']
    ];

    /**
     * Automatically creates a forum member for a user if not one already.
     * @param  RainLab\User\Models\User $user
     * @return RainLab\Forum\Models\Member
     */
    public static function getFromUser($user = null)
    {
        if ($user === null)
            $user = Auth::getUser();

        if (!$user)
            return null;

        if (!$user->forum_member) {
            $generatedUsername = explode('@', $user->email);
            $generatedUsername = array_shift($generatedUsername);
            $generatedUsername = Str::limit($generatedUsername, 24, '') . $user->id;

            $member = new static;
            $member->user = $user;
            $member->username = $generatedUsername;
            $member->save();

            $user->forum_member = $member;
        }

        return $user->forum_member;
    }
}
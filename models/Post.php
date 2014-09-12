<?php namespace RainLab\Forum\Models;

use App;
use Str;
use Model;
use Carbon\Carbon;
use October\Rain\Support\Markdown;

/**
 * Post Model
 */
class Post extends Model
{
    use \October\Rain\Database\Traits\Validation;

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
     * @var array The attributes that should be visible in arrays.
     */
    protected $visible = ['subject', 'content', 'member', 'topic'];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'topic_id' => 'required',
        'member_id' => 'required',
        'content' => 'required'
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
        $post->save();

        TopicFollow::follow($topic, $member);
        $member->touchActivity();
        return $post;
    }

    /**
     * Lists topics for the front end
     * @param  integer  $page      Page number
     * @param  string   $sort      Sorting field
     * @param  Channel  $channels  Topics in channels
     * @param  string   $search    Search query
     * @return self
     */
    public function scopeListFrontEnd($query, $page = 1, $sort = 'created_at', $topic, $search = '')
    {
        App::make('paginator')->setCurrentPage($page);
        $search = trim($search);

        $allowedSortingOptions = ['created_at', 'updated_at'];
        if (!in_array($sort, $allowedSortingOptions))
            $sort = $allowedSortingOptions[0];

        $query->orderBy($sort, 'asc');

        if (strlen($search)) {
            $query->searchWhere($search, ['subject', 'content']);
        }

        if ($topic) {
            $query->where('topic_id', $topic);
        }

        return $query->paginate(30);
    }

    public function canEdit($member = null)
    {
        if ($member === null)
            $member = Member::getFromUser();

        if (!$member)
            return false;

        if ($member->is_moderator)
            return true;

        return $this->member_id == $member->id;
    }

    //
    // Events
    //

    public function beforeSave()
    {
        $this->content_html = Str::cleanHtml(Markdown::parse(trim($this->content)));
    }

    public function afterCreate()
    {
        $this->member()->increment('count_posts');

        $this->topic->count_posts++;
        $this->topic->last_post_at = new Carbon;
        $this->topic->last_post = $this;
        $this->topic->last_post_member = $this->member;
        $this->topic->save();
        $this->topic->channel()->increment('count_posts');
    }

    public function afterDelete()
    {
        $this->member()->decrement('count_posts');
        $this->topic()->decrement('count_posts');
        $this->topic->channel()->decrement('count_posts');

        // If the topic has no more posts, delete it
        if ($this->topic->count_posts <= 0)
            $this->topic->delete();
    }

}
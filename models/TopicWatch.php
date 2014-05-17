<?php namespace RainLab\Forum\Models;

use Model;
use Carbon\Carbon;

/**
 * Topic watching model
 */
class TopicWatch extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'rainlab_forum_topic_watches';

    public $dates = ['watched_at'];

    public $timestamps = false;

    /**
     * Flag a topic as being watched by a member
     * @param Topic $topic   Forum topic
     * @param Member $member Forum member
     */
    public static function flagAsWatched($topic, $member)
    {
        $obj = self::where('member_id', $member->id)->where('topic_id', $topic->id)->first();
        if (!$obj) {
            $obj = new self;
            $obj->member_id = $member->id;
            $obj->topic_id = $topic->id;
        }

        $obj->watched_at = Carbon::now();
        $obj->count_posts = $topic->count_posts;
        $obj->save();
    }

    /**
     * Sets the watched flag (hasNew) for an array of topics
     * @param array $topics  Collection of topics
     * @param Member $member Forum member
     */
    public static function setFlagsOnTopics($topics, $member)
    {
        if (!count($topics)) return;

        $modelKeys = [];
        foreach ($topics as $topic) {
            $modelKeys[] = $topic->getKey();
        }

        $watches = self::where('member_id', $member->id)
            ->whereIn('topic_id', $modelKeys)
            ->get();

        foreach ($watches as $watch) {
            foreach ($topics as $topic) {
                if ($topic->id == $watch->topic_id)
                    $topic->hasNew = $topic->last_post_at->gt($watch->watched_at);
            }
        }

        return $topics;
    }

}
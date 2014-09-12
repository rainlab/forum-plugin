<?php namespace RainLab\Forum\Models;

use Model;
use Carbon\Carbon;

/**
 * Channel watching model
 */
class ChannelWatch extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'rainlab_forum_channel_watches';

    public $dates = ['watched_at'];

    public $timestamps = false;

    /**
     * Flag a channel as being watched by a member
     * @param Channel $channel   Forum channel
     * @param Member $member Forum member
     */
    public static function flagAsWatched($channel, $member)
    {
        $obj = self::where('member_id', $member->id)->where('channel_id', $channel->id)->first();
        if (!$obj) {
            $obj = new self;
            $obj->member_id = $member->id;
            $obj->channel_id = $channel->id;
        }

        $obj->watched_at = Carbon::now();
        $obj->count_topics = $channel->count_topics;
        $obj->save();
    }

    /**
     * Sets the watched flag (hasNew) for an array of channels
     * @param array $channels  Collection of channels
     * @param Member $member Forum member
     */
    public static function setFlagsOnChannels($channels, $member)
    {
        if (!count($channels)) return;

        $modelKeys = [];
        foreach ($channels as $channel) {
            $modelKeys[] = $channel->getKey();
        }

        $watches = self::where('member_id', $member->id)
            ->whereIn('channel_id', $modelKeys)
            ->get();

        foreach ($watches as $watch) {
            foreach ($channels as $channel) {
                if ($channel->id == $watch->channel_id) {
                    $channel->hasNew = ($firstTopic = $channel->first_topic)
                        ? $firstTopic->last_post_at->gt($watch->watched_at)
                        : false;
                }
            }
        }

        return $channels;
    }

}
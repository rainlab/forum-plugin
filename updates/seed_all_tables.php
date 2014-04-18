<?php namespace RainLab\Forum\Updates;

use RainLab\Forum\Models\Channel;
use October\Rain\Database\Updates\Seeder;

class SeedAllTables extends Seeder
{

    public function run()
    {
        $orange = Channel::create([
            'title' => 'Channel Orange',
            'description' => 'A root level forum channel',
        ]);

        $autumn = $orange->children()->create([
            'title' => 'Autumn Leaves',
            'description' => 'Disccusion about the season of falling leaves.'
        ]);

        $autumn->children()->create([
            'title' => 'September',
            'description' => 'The start of the fall season.'
        ]);

        $autumn->children()->create([
            'title' => 'October',
            'description' => 'The middle of the fall season.'
        ]);

        $autumn->children()->create([
            'title' => 'November',
            'description' => 'The end of the fall season.'
        ]);

        $orange->children()->create([
            'title' => 'Summer Breeze',
            'description' => 'Disccusion about the wind at the ocean.'
        ]);

        $green = Channel::create([
            'title' => 'Channel Green',
            'description' => 'A root level forum channel',
        ]);

        $green->children()->create([
            'title' => 'Winter Snow',
            'description' => 'Disccusion about the frosty snow flakes.'
        ]);

        $green->children()->create([
            'title' => 'Spring Trees',
            'description' => 'Disccusion about the blooming gardens.'
        ]);

        $user = \RainLab\User\Models\User::first();
        if (!$user) return;

        $member = \RainLab\Forum\Models\Member::getFromUser($user);

        \RainLab\Forum\Models\Topic::createInChannel($autumn, $member, [
            'subject' => 'First post!',
            'content' => 'Welcome to the forum!',
        ]);

        // $autumn->topics()->create(['subject' => 'First post!'])->posts()->create(['content' => 'Welcome to the forum!']);

        //
        // Code for the developing
        //
        // $topic = $autumn->topics()->create(['subject' => 'Lots of posts in here!']);
        // $topic->posts()->create(['subject' => 'First post', 'content' => 'This is a first post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);
        // $topic->posts()->create(['subject' => 'Another post', 'content' => 'This is a another post']);

        // $autumn->topics()->create(['subject' => 'Another post!'])->posts()->create(['content' => 'Another post']);
        // $autumn->topics()->create(['subject' => 'Another post!'])->posts()->create(['content' => 'Another post']);
        // $autumn->topics()->create(['subject' => 'Another post!'])->posts()->create(['content' => 'Another post']);
        // $autumn->topics()->create(['subject' => 'Another post!'])->posts()->create(['content' => 'Another post']);
        // $autumn->topics()->create(['subject' => 'Another post!'])->posts()->create(['content' => 'Another post']);
        // $autumn->topics()->create(['subject' => 'Another post!'])->posts()->create(['content' => 'Another post']);
        // $autumn->topics()->create(['subject' => 'Another post!'])->posts()->create(['content' => 'Another post']);
        // $autumn->topics()->create(['subject' => 'Another post!'])->posts()->create(['content' => 'Another post']);
        // $autumn->topics()->create(['subject' => 'Another post!'])->posts()->create(['content' => 'Another post']);
        // $autumn->topics()->create(['subject' => 'Another post!'])->posts()->create(['content' => 'Another post']);
        // $autumn->topics()->create(['subject' => 'Another post!'])->posts()->create(['content' => 'Another post']);
        // $autumn->topics()->create(['subject' => 'Another post!'])->posts()->create(['content' => 'Another post']);
        // $autumn->topics()->create(['subject' => 'Another post!'])->posts()->create(['content' => 'Another post']);

    }

}

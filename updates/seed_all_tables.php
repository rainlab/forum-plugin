<?php namespace RainLab\Forum\Updates;

use RainLab\Forum\Models\Post;
use RainLab\Forum\Models\Topic;
use RainLab\Forum\Models\Member;
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

        $october = $autumn->children()->create([
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

        $member = Member::getFromUser($user);

        Topic::createInChannel($october, $member, ['subject' => 'First post!', 'content' => 'Welcome to the forum!']);


        $topic = Topic::createInChannel($october, $member, ['subject' => 'Lots of posts in here!', 'content' => 'Another post!']);

        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);
        Post::createInTopic($topic, $member, ['content' => 'Test content']);


        Topic::createInChannel($october, $member, ['subject' => 'Plugins are the foundation for adding new features to the CMS by extending it', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'Define components', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'Define user permissions', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'Add back-end pages, menu items, and forms', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'Create database table structures and seed data', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'Alter functionality of the core or other plugins', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'Provide classes, back-end controllers, views, assets, and other files', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'Information about the plugin, its name, and author', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'Registration methods for extending the CMS', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'Injecting variables by participating in the page execution cycle', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'Handling AJAX events triggered by the page', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'Providing basic markup using partials', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'Pages - hold the content for each URL.', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'Partials - contain reusable chunks of HTML markup.', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'Layouts - define the page scaffold.', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'Content files - text or HTML blocks that can be edited separately from the page or layout.', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'Asset files - are resource files like images and stylesheets.', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'url - the page URL (required)', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'layout - the page layout, optional. If specified, should contain the name of the layout file, without extension. For example: "default".', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'description - the page description for the back-end interface', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'description - the partial description, for the back-end UI (optional)', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'description - the layout description, for the back-end UI (optional)', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'name - the layout name, for the back-end UI (optional)', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'htm - for HTML markup', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'txt - for plain text', 'content' => 'Another post content']);
        Topic::createInChannel($october, $member, ['subject' => 'md - for Markdown syntax', 'content' => 'Another post content']);

        Topic::createInChannel($october, $member, ['subject' => 'Moar posts!', 'content' => 'Another post content!']);

    }

}

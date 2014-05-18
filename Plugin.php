<?php namespace RainLab\Forum;

use Event;
use Backend;
use RainLab\User\Models\User;
use System\Classes\PluginBase;

/**
 * Forum Plugin Information File
 */
class Plugin extends PluginBase
{

    public $require = ['RainLab.User'];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Forum',
            'description' => 'A simple embeddable forum',
            'author'      => 'RainLab',
            'icon'        => 'icon-leaf'
        ];
    }

    public function boot()
    {
        User::extend(function($model) {
            $model->hasOne['forum_member'] = ['RainLab\Forum\Models\Member'];
        });

        Event::listen('backend.form.extendFields', function($widget) {
            if (!$widget->controller instanceof \RainLab\User\Controllers\Users) return;
            $widget->addFields([
                'forum_member[is_moderator]' => [
                    'label' => 'Forum moderator',
                    'type' => 'checkbox',
                    'tab' => 'Forum',
                    'span' => 'auto',
                ],
                'forum_member[is_banned]' => [
                    'label' => 'Banned from forum',
                    'type' => 'checkbox',
                    'tab' => 'Forum',
                    'span' => 'auto',
                ],
            ], 'primary');
        });
    }

    public function registerComponents()
    {
        return [
           '\RainLab\Forum\Components\Channels' => 'forumChannels',
           '\RainLab\Forum\Components\Channel'  => 'forumChannel',
           '\RainLab\Forum\Components\Topic'    => 'forumTopic',
           '\RainLab\Forum\Components\Member'   => 'forumMember',
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Forum Channels',
                'description' => 'Manage available forum channels.',
                'icon'        => 'icon-comments',
                'url'         => Backend::url('rainlab/forum/channels'),
                'order'       => 100
            ]
        ];
    }

    // public function registerNavigation()
    // {
    //     return [
    //         'forum' => [
    //             'label'       => 'Forum',
    //             'url'         => Backend::url('rainlab/forum/channels'),
    //             'icon'        => 'icon-comments',
    //             'permissions' => ['forum.*'],
    //             'order'       => 800,

    //             'sideMenu' => [
    //                 'topics' => [
    //                     'label'       => 'Topics',
    //                     'icon'        => 'icon-comments-o',
    //                     'url'         => Backend::url('rainlab/forum/topics'),
    //                     'permissions' => ['forum.access_topics'],
    //                 ],
    //                 'channels' => [
    //                     'label'       => 'Channels',
    //                     'icon'        => 'icon-list-ul',
    //                     'url'         => Backend::url('rainlab/forum/channels'),
    //                     'permissions' => ['forum.access_channels'],
    //                 ],
    //             ]

    //         ]
    //     ];
    // }

}

<?php namespace RainLab\Forum;

use Backend;
use RainLab\User\Models\User;
use System\Classes\PluginBase;

/**
 * Forum Plugin Information File
 */
class Plugin extends PluginBase
{

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
    }

    public function registerComponents()
    {
        return [
           '\RainLab\Forum\Components\ChannelList' => 'channelList',
           '\RainLab\Forum\Components\Channel'     => 'channel',
           '\RainLab\Forum\Components\Topic'       => 'topic',
           '\RainLab\Forum\Components\Member'      => 'member',
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

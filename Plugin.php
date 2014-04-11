<?php namespace RainLab\Forum;

use Backend;
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
            'description' => 'No description provided yet...',
            'author'      => 'RainLab',
            'icon'        => 'icon-leaf'
        ];
    }

    public function registerComponents()
    {
        return [
           '\RainLab\Forum\Components\ChannelList' => 'channelList',
           '\RainLab\Forum\Components\Channel'     => 'channel',
           '\RainLab\Forum\Components\Topic'       => 'topic',
        ];
    }

    public function registerNavigation()
    {
        return [
            'forum' => [
                'label'       => 'Forum',
                'url'         => Backend::url('rainlab/forum/topics'),
                'icon'        => 'icon-comments-alt',
                'permissions' => ['forum.*'],
                'order'       => 800,

                'sideMenu' => [
                    'topics' => [
                        'label'       => 'Topics',
                        'icon'        => 'icon-comments',
                        'url'         => Backend::url('rainlab/forum/topics'),
                        'permissions' => ['forum.access_topics'],
                    ],
                    'channels' => [
                        'label'       => 'Channels',
                        'icon'        => 'icon-list-ul',
                        'url'         => Backend::url('rainlab/forum/channels'),
                        'permissions' => ['forum.access_channels'],
                    ],
                ]

            ]
        ];
    }

}

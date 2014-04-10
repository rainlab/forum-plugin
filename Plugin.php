<?php namespace RainLab\Forum;

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


}

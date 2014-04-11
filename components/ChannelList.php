<?php namespace RainLab\Forum\Components;

use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Channel;

class ChannelList extends ComponentBase
{

    private $channels = null;

    public function componentDetails()
    {
        return [
            'name'        => 'Channel List',
            'description' => 'Displays a list of all visible channels.'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['channels'] = $this->listChannels();
    }

    protected function listChannels()
    {
        if ($this->channels !== null)
            return $this->channels;

        return $this->channels = Channel::make()->getRootChildren();
    }

}
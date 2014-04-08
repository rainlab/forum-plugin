<?php namespace RainLab\Forum\Components;

use Cms\Classes\ComponentBase;

class ChannelList extends ComponentBase
{

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

}
<?php namespace RainLab\Forum\Components;

use Cms\Classes\ComponentBase;

class Channel extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Channel',
            'description' => 'Displays a list of posts belonging to a channel.'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

}
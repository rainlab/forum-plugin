<?php namespace RainLab\Forum\Components;

use Cms\Classes\ComponentBase;

class Topic extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Topic',
            'description' => 'Displays a topic and posts.'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

}
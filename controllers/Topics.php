<?php namespace RainLab\Forum\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Topics Back-end Controller
 */
class Topics extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('RainLab.Forum', 'forum', 'topics');
    }
}
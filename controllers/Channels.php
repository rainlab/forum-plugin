<?php namespace RainLab\Forum\Controllers;

use Flash;
use BackendMenu;
use Backend\Classes\Controller;
use RainLab\Forum\Models\Channel;
use System\Classes\SettingsManager;

/**
 * Channels Back-end Controller
 */
class Channels extends Controller
{
    /**
     * @var array implement extensions
     */
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class
    ];

    /**
     * @var array formConfig configuration.
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var array listConfig configuration.
     */
    public $listConfig = 'config_list.yaml';

    /**
     * @var array relationConfig for extensions.
     */
    public $relationConfig;

    /**
     * @var array requiredPermissions to view this page.
     */
    public $requiredPermissions = ['rainlab.forum.manage_channels'];

    /**
     * __construct
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('RainLab.Forum', 'settings');
    }

    /**
     * index_onDelete
     */
    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            foreach ($checkedIds as $channelId) {
                if (!$channel = Channel::find($channelId)) {
                    continue;
                }

                $channel->delete();
            }

            Flash::success('Successfully deleted those channels.');
        }

        return $this->listRefresh();
    }
}

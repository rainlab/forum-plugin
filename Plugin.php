<?php namespace RainLab\Forum;

use Event;
use Backend;
use RainLab\User\Models\User;
use RainLab\Forum\Models\Member;
use System\Classes\PluginBase;
use RainLab\User\Controllers\Users as UsersController;

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
            'author'      => 'Alexey Bobkov, Samuel Georges',
            'homepage'    => 'http://octobercms.com',
            'icon'        => 'icon-comments'
        ];
    }

    public function boot()
    {
        User::extend(function($model) {
            $model->hasOne['forum_member'] = ['RainLab\Forum\Models\Member'];
        });

        UsersController::extendFormFields(function($widget, $model, $context) {
            if ($context != 'update') return;
            if (!Member::getFromUser($model)) return;

            $widget->addFields([
                'forum_member[username]' => [
                    'label'   => 'Username',
                    'tab'     => 'Forum',
                    'comment' => 'The display to represent this user on the forum.',
                ],
                'forum_member[is_moderator]' => [
                    'label'   => 'Forum moderator',
                    'type'    => 'checkbox',
                    'tab'     => 'Forum',
                    'span'    => 'auto',
                    'comment' => 'Place a tick in this box if this user can moderate the entire forum.',
                ],
                'forum_member[is_banned]' => [
                    'label'   => 'Banned from forum',
                    'type'    => 'checkbox',
                    'tab'     => 'Forum',
                    'span'    => 'auto',
                    'comment' => 'Place a tick in this box if this user is banned from posting to the forum.',
                ],
            ], 'primary');
        });

        UsersController::extendListColumns(function($widget, $model) {
            if (!$model instanceof \RainLab\User\Models\User) return;

            $widget->addColumns([
                'forum_member_username' => [
                    'label'      => 'Forum Username',
                    'relation'   => 'forum_member',
                    'select'     => '@username',
                    'searchable' => true,
                ]
            ]);
        });
    }

    public function registerComponents()
    {
        return [
           '\RainLab\Forum\Components\Channels'     => 'forumChannels',
           '\RainLab\Forum\Components\Channel'      => 'forumChannel',
           '\RainLab\Forum\Components\Topic'        => 'forumTopic',
           '\RainLab\Forum\Components\Topics'       => 'forumTopics',
           '\RainLab\Forum\Components\Member'       => 'forumMember',
           '\RainLab\Forum\Components\EmbedTopic'   => 'forumEmbedTopic',
           '\RainLab\Forum\Components\EmbedChannel' => 'forumEmbedChannel',
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Forum channels',
                'description' => 'Manage available forum channels.',
                'icon'        => 'icon-comments',
                'url'         => Backend::url('rainlab/forum/channels'),
                'category'    => 'Forum',
                'order'       => 500,
            ]
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'rainlab.forum::mail.topic_reply' => 'Notification to followers when a post is made to a topic.',
            'rainlab.forum::mail.member_report' => 'Notification to moderators when a member is reported to be a spammer.',
        ];
    }

}

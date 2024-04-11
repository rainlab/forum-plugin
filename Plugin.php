<?php namespace RainLab\Forum;

use Backend;
use RainLab\User\Models\User;
use RainLab\Forum\Models\Member;
use System\Classes\PluginBase;
use RainLab\User\Controllers\Users as UsersController;

/**
 * Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array require other plugins
     */
    public $require = [
        'RainLab.User'
    ];

    /**
     * pluginDetails returns information about this plugin.
     */
    public function pluginDetails()
    {
        return [
            'name' => 'rainlab.forum::lang.plugin.name',
            'description' => 'rainlab.forum::lang.plugin.description',
            'author' => 'Alexey Bobkov, Samuel Georges',
            'icon' => 'icon-comments',
            'homepage' => 'https://github.com/rainlab/forum-plugin'
        ];
    }

    /**
     * boot
     */
    public function boot()
    {
        User::extend(function($model) {
            $model->hasOne['forum_member'] = ['RainLab\Forum\Models\Member'];

            $model->bindEvent('model.beforeDelete', function() use ($model) {
                $model->forum_member && $model->forum_member->delete();
            });
        });

        UsersController::extendFormFields(function($widget, $model, $context) {
            // Prevent extending of related form instead of the intended User form
            if (!$widget->model instanceof \RainLab\User\Models\User) {
                return;
            }
            if ($context != 'update') {
                return;
            }
            if (!Member::getFromUser($model)) {
                return;
            }

            $widget->addFields([
                'forum_member[username]' => [
                    'label' => 'rainlab.forum::lang.settings.username',
                    'tab' => 'Forum',
                    'comment' => 'rainlab.forum::lang.settings.username_comment'
                ],
                'forum_member[is_moderator]' => [
                    'label' => 'rainlab.forum::lang.settings.moderator',
                    'type' => 'checkbox',
                    'tab' => 'Forum',
                    'span' => 'auto',
                    'comment' => 'rainlab.forum::lang.settings.moderator_comment'
                ],
                'forum_member[is_banned]' => [
                    'label' => 'rainlab.forum::lang.settings.banned',
                    'type' => 'checkbox',
                    'tab' => 'Forum',
                    'span' => 'auto',
                    'comment' => 'rainlab.forum::lang.settings.banned_comment'
                ]
            ], 'primary');
        });

        UsersController::extendListColumns(function($widget, $model) {
            if (!$model instanceof \RainLab\User\Models\User) {
                return;
            }

            $widget->addColumns([
                'forum_member_username' => [
                    'label' => 'rainlab.forum::lang.settings.forum_username',
                    'relation' => 'forum_member',
                    'select' => 'username',
                    'searchable' => false,
                    'invisible' => true
                ]
            ]);
        });
    }

    /**
     * registerComponents
     */
    public function registerComponents()
    {
        return [
           \RainLab\Forum\Components\Channels::class => 'forumChannels',
           \RainLab\Forum\Components\Channel::class => 'forumChannel',
           \RainLab\Forum\Components\Topic::class => 'forumTopic',
           \RainLab\Forum\Components\Topics::class => 'forumTopics',
           \RainLab\Forum\Components\Posts::class => 'forumPosts',
           \RainLab\Forum\Components\Member::class => 'forumMember',
           \RainLab\Forum\Components\EmbedTopic::class => 'forumEmbedTopic',
           \RainLab\Forum\Components\EmbedChannel::class => 'forumEmbedChannel',
           \RainLab\Forum\Components\RssFeed::class => 'forumRssFeed'
        ];
    }

    /**
     * registerPermissions
     */
    public function registerPermissions()
    {
        return [
            'rainlab.forum.manage_channels' => [
                'tab' => 'rainlab.forum::lang.settings.channels',
                'label' => 'rainlab.forum::lang.settings.channels_desc'
            ]
        ];
    }

    /**
     * registerSettings
     */
    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'rainlab.forum::lang.settings.channels',
                'description' => 'rainlab.forum::lang.settings.channels_desc',
                'icon' => 'icon-comments',
                'url' => Backend::url('rainlab/forum/channels'),
                'category' => 'rainlab.forum::lang.plugin.name',
                'order' => 500,
                'permissions' => ['rainlab.forum.manage_channels'],
            ]
        ];
    }

    /**
     * registerMailTemplates
     */
    public function registerMailTemplates()
    {
        return [
            'rainlab.forum::mail.topic_reply',
            'rainlab.forum::mail.member_report'
        ];
    }
}

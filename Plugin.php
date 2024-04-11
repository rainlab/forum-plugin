<?php namespace RainLab\Forum;

use Backend;
use RainLab\User\Models\User;
use RainLab\Forum\Models\Member;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;
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
            'name' => "Forum",
            'description' => "A simple embeddable forum",
            'author' => 'Alexey Bobkov, Samuel Georges',
            'icon' => 'icon-comments',
            'homepage' => 'https://github.com/rainlab/forum-plugin'
        ];
    }

    /**
     * register the service provider.
     */
    public function register()
    {
        $this->registerSingletons();
    }

    /**
     * boot
     */
    public function boot()
    {
        User::extend(function($model) {
            $model->hasOne['forum_member'] = \RainLab\Forum\Models\Member::class;

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
                    'label' => "Username",
                    'comment' => "The display to represent this user on the forum.",
                    'tab' => 'Forum'
                ],
                'forum_member[is_moderator]' => [
                    'label' => "Forum moderator",
                    'comment' => "Place a tick in this box if this user can moderate the entire forum.",
                    'type' => 'checkbox',
                    'tab' => 'Forum',
                    'span' => 'auto'
                ],
                'forum_member[is_banned]' => [
                    'label' => "Banned from forum",
                    'comment' => "Place a tick in this box if this user is banned from posting to the forum.",
                    'type' => 'checkbox',
                    'tab' => 'Forum',
                    'span' => 'auto'
                ]
            ], 'primary');
        });

        UsersController::extendListColumns(function($widget, $model) {
            if (!$model instanceof \RainLab\User\Models\User) {
                return;
            }

            $widget->addColumns([
                'forum_member_username' => [
                    'label' => "Forum Username",
                    'relation' => 'forum_member',
                    'select' => 'username',
                    'searchable' => false,
                    'invisible' => true
                ]
            ]);
        });
    }

    /**
     * registerSingletons
     */
    protected function registerSingletons()
    {
        $this->app->singleton('rainlab.forum.tracker', \RainLab\Forum\Classes\TopicTracker::class);
    }

    /**
     * registerComponents
     */
    public function registerComponents()
    {
        return [
           \RainLab\Forum\Components\ForumChannel::class => 'forumChannel',
           \RainLab\Forum\Components\ForumChannels::class => 'forumChannels',
           \RainLab\Forum\Components\ForumTopic::class => 'forumTopic',
           \RainLab\Forum\Components\ForumTopics::class => 'forumTopics',
           \RainLab\Forum\Components\ForumPosts::class => 'forumPosts',
           \RainLab\Forum\Components\ForumMember::class => 'forumMember',
           \RainLab\Forum\Components\ForumEmbedTopic::class => 'forumEmbedTopic',
           \RainLab\Forum\Components\ForumEmbedChannel::class => 'forumEmbedChannel',
           \RainLab\Forum\Components\ForumRssFeed::class => 'forumRssFeed'
        ];
    }

    /**
     * registerPermissions
     */
    public function registerPermissions()
    {
        return [
            'rainlab.forum.manage_channels' => [
                'tab' => "Forum Channels",
                'label' => "Manage available forum channels."
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
                'label' => "Forum Channels",
                'description' => "Manage available forum channels.",
                'icon' => 'icon-comments',
                'url' => Backend::url('rainlab/forum/channels'),
                'category' => SettingsManager::CATEGORY_USERS,
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
            'rainlab.forum:topic_reply' => 'rainlab.forum::mail.topic_reply',
            'rainlab.forum:member_report' => 'rainlab.forum::mail.member_report'
        ];
    }
}

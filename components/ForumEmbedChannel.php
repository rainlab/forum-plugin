<?php namespace RainLab\Forum\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Channel as ChannelModel;
use Exception;

/**
 * ForumEmbedChannel will embed a channel inside a page
 */
class ForumEmbedChannel extends ComponentBase
{
    /**
     * @var bool embedMode determines if this component is being used by the EmbedChannel component.
     */
    public $embedMode = true;

    /**
     * componentDetails
     */
    public function componentDetails()
    {
        return [
            'name' => "Embed Channel",
            'description' => "Attach a channel to any page."
        ];
    }

    /**
     * defineProperties
     */
    public function defineProperties()
    {
        return [
            'embedCode' => [
                'title' => "Embed code param",
                'description' => "A unique code for the generated channel. A routing parameter can also be used.",
                'type' => 'string',
                'group' => 'Parameters',
            ],
            'channelSlug' => [
                'title' => "Parent Channel",
                'description' => "Specify the channel to create the new channel in",
                'type' => 'dropdown'
            ],
            'topicSlug' => [
                'title' => "Topic code param",
                'description' => "The URL route parameter used for looking up a topic by its slug.",
                'type' => 'string',
                'default' => '{{ :topicSlug }}',
                'group' => 'Parameters',
            ],
            'memberPage' => [
                'title' => "Member Page",
                'description' => "Page name to use for clicking on a Member.",
                'type' => 'dropdown',
                'group' => 'Links',
            ],
            'isGuarded' => [
                'title' => 'Spam Guarded Channel',
                'description' => 'Newly created channels will have spam guard enabled',
                'type' => 'checkbox',
                'default' => 0,
                'group' => 'Parameters',
            ],
        ];
    }

    /**
     * getChannelSlugOptions
     */
    public function getChannelSlugOptions()
    {
        return ChannelModel::listsNested('title', 'slug', ' - ');
    }

    /**
     * getMemberPageOptions
     */
    public function getMemberPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * init
     */
    public function init()
    {
        $code = $this->property('embedCode');

        if (!$code) {
            throw new Exception('No code specified for the Forum Embed component');
        }

        $parentChannel = ($channelSlug = $this->property('channelSlug'))
            ? ChannelModel::whereSlug($channelSlug)->first()
            : null;

        if (!$parentChannel) {
            throw new Exception('No channel specified for Forum Embed component');
        }

        $properties = $this->getProperties();

        // Proxy as topic
        if (input('channel') || $this->property('topicSlug')) {
            $properties['slug'] = '{{ ' . $this->propertyName('topicSlug') . ' }}';
            $component = $this->addComponent(\RainLab\Forum\Components\ForumTopic::class, $this->alias, $properties);
        }
        // Proxy as channel
        else {
            if ($channel = ChannelModel::forEmbed($parentChannel, $code)->first()) {
                $properties['slug'] = $channel->slug;
            }

            $properties['topicPage'] = $this->page->baseFileName;
            $component = $this->addComponent(\RainLab\Forum\Components\ForumChannel::class, $this->alias, $properties);
            $component->embedTopicParam = $this->paramName('topicSlug');

            // If a channel does not already exist, generate it when the page ends.
            // This can be disabled by the page setting embedMode to FALSE, for example,
            // if the page returns 404 a channel should not be generated.
            if (!$channel) {
                $this->controller->bindEvent('page.end', function() use ($component, $parentChannel, $code) {
                    if ($component->embedMode !== false) {
                        $channel = ChannelModel::createForEmbed(
                            $code,
                            $parentChannel,
                            $this->page->title,
                            (bool) $this->property('isGuarded')
                        );
                        $component->setProperty('slug', $channel->slug);
                        $component->onRun();
                    }
                });
            }
        }

        // Set the default embedding mode
        if (input('channel')) {
            $component->embedMode = 'post';
        }
        elseif (input('search')) {
            $component->embedMode = 'search';
        }
        elseif ($this->property('topicSlug')) {
            $component->embedMode = 'topic';
        }
        else {
            $component->embedMode = 'channel';
        }
    }
}

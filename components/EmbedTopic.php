<?php namespace RainLab\Forum\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Topic as TopicModel;
use RainLab\Forum\Models\Channel as ChannelModel;
use Exception;

class EmbedTopic extends ComponentBase
{

    public $embedMode = true;

    public function componentDetails()
    {
        return [
            'name'        => 'Embed Topic',
            'description' => 'Attach a topic to any page.'
        ];
    }

    public function defineProperties()
    {
        return [
            'idParam' => [
                'title'             => 'Embed Code',
                'description'       => 'A unique code for the generated topic or channel. A routing parameter can also be used.',
                'type'              => 'string',
            ],
            'channelId' => [
                'title'             => 'Target Channel',
                'description'       => 'Specify the channel to create the new topic or channel in',
                'type'              => 'dropdown'
            ],
            'memberPage' => [
                'title'             => 'Member Page',
                'description'       => 'Page name to use for clicking on a member.',
                'type'              => 'dropdown',
            ],
            'memberPageIdParam' => [
                'title'             => 'Member page param name',
                'description'       => 'The expected parameter name used when creating links to the member page.',
                'type'              => 'string',
                'default'           => ':slug',
            ],
        ];
    }

    protected function getChannelIdOptions()
    {
        return ChannelModel::orderByNested()->listsNested('title', 'slug', ' - ');
    }

    public function getMemberPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onInit()
    {
        $mode = $this->property('mode');
        $code = $this->propertyOrParam('idParam');

        if (!$code)
            throw new Exception('No code specified for the Forum Embed component');

        $channel = ($channelId = $this->property('channelId'))
            ? ChannelModel::whereSlug($channelId)->first()
            : null;

        if (!$channel)
            throw new Exception('No channel specified for Forum Embed component');

        $properties = $this->getProperties();

        /*
         * Proxy as topic
         */
        if ($topic = TopicModel::forEmbed($channel, $code)->first())
            $properties['idParam'] = $topic->slug;

        $component = $this->addComponent('RainLab\Forum\Components\Topic', $this->alias, $properties);

        /*
         * If a topic does not already exist, generate it when the page ends.
         * This can be disabled by the page setting embedMode to FALSE, for example,
         * if the page returns 404 a topic should not be generated.
         */
        if (!$topic) {
            $this->controller->bindEvent('page.end', function() use ($component, $channel, $code) {
                if ($component->embedMode !== false) {
                    $topic = TopicModel::createForEmbed($code, $channel, $this->page->title);
                    $component->setProperty('idParam', $topic->slug);
                    $component->onRun();
                }
            });
        }

        /*
         * Set the embedding mode: Single topic
         */
        $component->embedMode = 'single';

    }

}
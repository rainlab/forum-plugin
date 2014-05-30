<?php namespace RainLab\Forum\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Topic as TopicModel;
use RainLab\Forum\Models\Channel as ChannelModel;
use Exception;

class EmbedChannel extends ComponentBase
{

    public $embedMode = true;

    public function componentDetails()
    {
        return [
            'name'        => 'Embed Channel',
            'description' => 'Attach a channel to any page.'
        ];
    }

    public function defineProperties()
    {
        return [
            'idParam' => [
                'title'             => 'Embed code param',
                'description'       => 'A unique code for the generated channel. A routing parameter can also be used.',
                'type'              => 'string',
            ],
            'topicParam' => [
                'title'             => 'Topic code param',
                'description'       => 'The URL route parameter used for looking up a topic by its slug.',
                'type'              => 'string',
                'default'           => ':topicSlug',
            ],
            'channelId' => [
                'title'             => 'Parent Channel',
                'description'       => 'Specify the channel to create the new channel in',
                'type'              => 'dropdown'
            ],
            'memberPage' => [
                'title'             => 'Member Page',
                'description'       => 'Page name to use for clicking on a member.',
                'type'              => 'dropdown',
            ],
        ];
    }

    protected function getChannelIdOptions()
    {
        return ChannelModel::orderBy('title')->lists('title', 'slug');
    }

    public function getMemberPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onInit()
    {
        $code = $this->propertyOrParam('idParam');

        if (!$code)
            throw new Exception('No code specified for the Forum Embed component');

        $channel = ($channelId = $this->property('channelId'))
            ? ChannelModel::whereSlug($channelId)->first()
            : null;

        if (!$channel)
            throw new Exception('No channel specified for Forum Embed component');

        if (post('channel') || $this->propertyOrParam('topicParam')) {
            $properties = $this->getProperties();
            $properties['idParam'] = $this->property('topicParam');
            $component = $this->addComponent('RainLab\Forum\Components\Topic', $this->alias, $properties);
        }
        else {
            $channel = ChannelModel::createForEmbed($code, $channelId, $this->page->title);
            $properties = $this->getProperties();
            $properties['idParam'] = $channel->slug;
            $properties['topicPage'] = $this->page->baseFileName;
            $properties['topicPageIdParam'] = $this->property('topicParam');

            // Replace this component completely
            $component = $this->addComponent('RainLab\Forum\Components\Channel', $this->alias, $properties);
        }

        /*
         * Set the embedding mode
         */
        if (post('channel'))
            $component->embedMode = 'post';
        elseif (post('search'))
            $component->embedMode = 'search';
        elseif ($this->propertyOrParam('topicParam'))
            $component->embedMode = 'topic';
        else
            $component->embedMode = 'channel';
    }

}
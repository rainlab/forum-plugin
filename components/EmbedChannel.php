<?php namespace RainLab\Forum\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Topic as TopicModel;
use RainLab\Forum\Models\Channel as ChannelModel;

class EmbedChannel extends ComponentBase
{

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
            'paramId' => [
                'title'             => 'Embed code param',
                'description'       => 'A unique code for the generated channel. A routing parameter can also be used.',
                'type'              => 'string',
            ],
            'paramTopic' => [
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
        return ChannelModel::orderBy('title')->lists('title', 'id');
    }

    public function getMemberPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onInit()
    {
        $code = $this->propertyOrParam('paramId');

        if (!$code)
            return 'No code specified for the Forum Embed component';

        $channel = ($channelId = $this->property('channelId'))
            ? ChannelModel::find($channelId)
            : null;

        if (!$channel)
            return 'No channel specified for Forum Embed component';

        if (post('channel') || $this->propertyOrParam('paramTopic')) {
            $properties = $this->getProperties();
            $properties['paramId'] = $this->property('paramTopic');
            $component = $this->addComponent('RainLab\Forum\Components\Topic', $this->alias, $properties);
            $component->embedMode = true;
        }
        else {
            $channel = ChannelModel::createForEmbed($code, $channelId, $this->page->title);
            $properties = $this->getProperties();
            $properties['paramId'] = $channel->slug;
            $properties['topicPage'] = $this->page->baseFileName;
            $properties['topicPageParamId'] = $this->property('paramTopic');

            // Replace this component completely
            $this->addComponent('RainLab\Forum\Components\Channel', $this->alias, $properties);
        }

    }

}
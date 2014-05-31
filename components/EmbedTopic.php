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
            ? ChannelModel::find($channelId)
            : null;

        if (!$channel)
            throw new Exception('No channel specified for Forum Embed component');

        $topic = TopicModel::createForEmbed($code, $channelId, $this->page->title);

        $properties = $this->getProperties();
        $properties['idParam'] = $topic->slug;

        // Replace this component completely
        $component = $this->addComponent('RainLab\Forum\Components\Topic', $this->alias, $properties);
        $component->embedMode = 'topic';
    }

}
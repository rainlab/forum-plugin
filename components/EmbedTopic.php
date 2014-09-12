<?php namespace RainLab\Forum\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Topic as TopicModel;
use RainLab\Forum\Models\Channel as ChannelModel;
use Exception;

class EmbedTopic extends ComponentBase
{

    /**
     * @var boolean Determine if this component is being used by the EmbedChannel component.
     */
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
            'channelId' => [
                'title'             => 'Target Channel',
                'description'       => 'Specify the channel to create the new topic or channel in',
                'type'              => 'dropdown'
            ],
            'idParam' => [
                'title'             => 'Embed Code',
                'description'       => 'A unique code for the generated topic or channel. A routing parameter can also be used.',
                'type'              => 'string',
            ],
            'memberPage' => [
                'title'             => 'rainlab.forum::lang.member.page_name',
                'description'       => 'rainlab.forum::lang.member.page_help',
                'type'              => 'dropdown',
                'group'             => 'Links',
            ],
        ];
    }

    protected function getChannelIdOptions()
    {
        return ChannelModel::listsNested('title', 'slug', ' - ');
    }

    public function getMemberPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function init()
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
<?php namespace RainLab\Forum\Components;

use Cms\Classes\ComponentBase;
use RainLab\Forum\Models\Member as MemberModel;

class Member extends ComponentBase
{

    private $member = null;

    const PARAM_SLUG = 'slug';

    public function componentDetails()
    {
        return [
            'name'        => 'Member',
            'description' => 'Displays form member information and activity.'
        ];
    }

    public function defineProperties()
    {
        return [
            'channelPage' => [
                'title'       => 'Channel Page',
                'description' => 'Page name to use for clicking on a channel.',
                'type'        => 'string' // @todo Page picker
            ],
            'topicPage' => [
                'title'       => 'Topic Page',
                'description' => 'Page name to use for clicking on a conversation topic.',
                'type'        => 'string' // @todo Page picker
            ],
        ];
    }

    public function onRun()
    {
        $this->addCss('/plugins/rainlab/forum/assets/css/forum.css');

        $this->page['member'] = $this->getMember();
        $this->prepareVars();
    }

    public function getMember()
    {
        if ($this->member !== null)
            return $this->member;

        if (!$slug = $this->param(static::PARAM_SLUG))
            return null;

        $member = MemberModel::whereSlug($slug)->first();
        return $this->member = $member;
    }

    protected function prepareVars()
    {
        /*
         * Load the page links
         */
        $links = [
            'channel' => $this->property('channelPage'),
            'topic' => $this->property('topicPage'),
        ];

        $this->page['forumLink'] = $links;
    }

}
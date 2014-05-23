<?php namespace RainLab\Forum\Components;

use Flash;
use Redirect;
use Cms\Classes\Page;
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
                'type'        => 'dropdown',
            ],
            'topicPage' => [
                'title'       => 'Topic Page',
                'description' => 'Page name to use for clicking on a conversation topic.',
                'type'        => 'dropdown',
            ],
            'viewMode' => [
                'title'       => 'View mode',
                'description' => 'Manually set the view mode for the member component.',
                'type'        => 'dropdown',
                'default'     => ''
            ],
        ];
    }

    public function getViewModeOptions()
    {
        return ['' => '- none -', 'view' => 'View', 'edit' => 'Edit'];
    }

    public function getPropertyOptions($property)
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
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
            $member = MemberModel::getFromUser();
        else
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
        $this->page['canEdit'] = $this->canEdit();
        $this->page['mode'] = $this->getMode();
    }

    public function getMode()
    {
        return $this->property('viewMode', post('mode', 'view'));
    }

    public function canEdit()
    {
        if ($this->property('viewMode') == 'view')
            return false;

        if (!$member = $this->getMember())
            return false;

        return $member->canEdit(MemberModel::getFromUser());
    }

    public function onUpdate()
    {
        try {
            if (!$this->canEdit())
                throw new ApplicationException('Permission denied.');

            $member = $this->getMember();
            if (!$member) return;

            $member->save(post());

            Flash::success(post('flash', 'Settings successfully saved!'));

            /*
             * Redirect to the intended page after successful update
             */
            $redirectUrl = post('redirect', $this->currentPageUrl([
                'slug' => $member->slug
            ]));

            return Redirect::to($redirectUrl);
        }
        catch (\Exception $ex) {
            Flash::error($ex->getMessage());
        }
    }

}
<?php

namespace App\Actions;

use TCG\Voyager\Actions\AbstractAction;

class AttributeValueAction extends AbstractAction
{
    public function getTitle()
    {
        // Action title which display in button based on current status
//        return $this->data->{'status'}=="PUBLISHED"?'Pending':'Publish';
        return 'Values';
    }

    public function getIcon()
    {
        // Action icon which display in left of button based on current status
//        return $this->data->{'status'}=="PUBLISHED"?'voyager-x':'voyager-external';
        return 'voyager-external';
    }

    public function getAttributes()
    {
        // Action button class
        return [
            'class' => 'btn btn-sm btn-primary pull-right pr-1',
            'style' => 'margin-right:5px',
        ];
    }

    public function shouldActionDisplayOnDataType()
    {
        // show or hide the action button, in this case will show for posts model
        return $this->dataType->slug == 'attributes';
    }

    public function getDefaultRoute()
    {
        // URL for action button when click
//        return route('posts.publish', array("id"=>$this->data->{$this->data->getKeyName()}));
        return '#';
    }
}

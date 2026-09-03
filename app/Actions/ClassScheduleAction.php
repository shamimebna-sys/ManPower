<?php

namespace App\Actions;

use TCG\Voyager\Actions\AbstractAction;

class ClassScheduleAction extends AbstractAction
{
    public function getTitle()
    {
        return 'Schedule';
    }

    public function getIcon()
    {
        return 'voyager-external';
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-primary pull-right pr-1',
            'style' => 'margin-right:5px',
        ];
    }

    public function shouldActionDisplayOnDataType()
    {
        return $this->dataType->slug == 'teachers';
    }

    public function getDefaultRoute()
    {
//        return route('admin.purchase.edit', array("id"=>$this->data->{$this->data->getKeyName()}));
        return route('voyager.class-schedules.create').'?teacher_id='.$this->data->{$this->data->getKeyName()};
//        return '#'.$this->data->{$this->data->getKeyName()};
//        return 'javascript:alert("22");'.$this->data->{$this->data->getKeyName()};
//        return 'javascript:openModal("'.$this->data->{$this->data->getKeyName()}.'");';
    }
}

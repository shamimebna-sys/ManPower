<?php

namespace App\Actions;

use TCG\Voyager\Actions\AbstractAction;

class InvoicePrintAction extends AbstractAction
{
    public function getTitle()
    {
        return 'Export Invoice';
    }

    public function getIcon()
    {
        return 'voyager-polaroid';
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-success pull-right pr-1',
            'style' => 'margin-right:5px',
            'target' => '_blank', // 🔥 open in new tab
        ];
    }

    public function shouldActionDisplayOnDataType()
    {
        return $this->dataType->slug == 'invoices';
    }

    public function getDefaultRoute()
    {
        return route('admin.reports.generate.invoice-report', $this->data->{$this->data->getKeyName()});
//        return route('voyager.payments.create').'?agent_id='.$this->data->{$this->data->getKeyName()};
//        return '#'.$this->data->{$this->data->getKeyName()};
//        return 'javascript:alert("22");'.$this->data->{$this->data->getKeyName()};
//        return 'javascript:openModal("'.$this->data->{$this->data->getKeyName()}.'");';
    }
}

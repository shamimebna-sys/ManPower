<?php

namespace App\Actions;

use TCG\Voyager\Actions\AbstractAction;

class PaymentApprovalAction extends AbstractAction
{
    public function getTitle()
    {
        return 'Approval';
    }

    public function getIcon()
    {
        return 'voyager-external';
    }

    public function getAttributes()
    {

        $display = ($this->data->status =='P')?'block':'none';

        return [
            'class' => 'btn btn-sm btn-primary pull-right pr-1',
            'style' => 'margin-right:5px; display:'.$display,
        ];
    }

    public function shouldActionDisplayOnDataType()
    {
        return $this->dataType->slug == 'payments';
    }

    public function getDefaultRoute()
    {
        return 'javascript:openModal("'.route("admin.ajax.payment-approval", $this->data->{$this->data->getKeyName()}).'", false, "modal-lg", "Payment Approval");';
    }
}

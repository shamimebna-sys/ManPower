<?php

namespace App\Actions;

use App\Models\InvoiceMoneyReceipt;
use TCG\Voyager\Actions\AbstractAction;

class InvoiceMoneyReceiptAction extends AbstractAction
{
    protected $hasMoneyReceipt = false;

    protected function checkReceipt()
    {
        if ($this->hasMoneyReceipt !== false) {
            return $this->hasMoneyReceipt;
        }

        $invoiceId = $this->data->{$this->data->getKeyName()};
        $invoice = InvoiceMoneyReceipt::where('invoice_id', $invoiceId)->first();
        $this->hasMoneyReceipt = $invoice ? true : false;

        return $this->hasMoneyReceipt;
    }

    public function getTitle()
    {
        return $this->checkReceipt() ? 'Export Receipt' : 'Add Receipt';
    }

    public function getIcon()
    {
        return $this->checkReceipt() ? 'voyager-polaroid' : 'voyager-external';
    }

    public function getAttributes()
    {
        $color = $this->checkReceipt() ? 'success' : 'primary';
        return [
            'class' => "btn btn-sm btn-$color pull-right pr-1",
            'style' => 'margin-right:5px',
            'target' => $this->checkReceipt() ? '_blank' : '',
        ];
    }

    public function shouldActionDisplayOnDataType()
    {
        return $this->dataType->slug == 'invoices';
    }

    public function getDefaultRoute()
    {
        if ($this->checkReceipt()) {
            return route('admin.reports.generate.invoice-money-receipts-report', $this->data->{$this->data->getKeyName()});
        }

        return route('voyager.invoice-money-receipts.create') . '?invoice_id=' . $this->data->{$this->data->getKeyName()};
    }
}

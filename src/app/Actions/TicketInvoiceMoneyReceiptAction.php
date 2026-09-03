<?php

namespace App\Actions;

use App\Models\TicketInvoiceMoneyReceipt as InvoiceMoneyReceipt ;
use TCG\Voyager\Actions\AbstractAction;

class TicketInvoiceMoneyReceiptAction extends AbstractAction
{
    protected $hasMoneyReceipt = false;

    protected function checkReceipt()
    {
        if ($this->hasMoneyReceipt !== false) {
            return $this->hasMoneyReceipt;
        }

        $invoiceId = $this->data->{$this->data->getKeyName()};
        $invoice = InvoiceMoneyReceipt::where('ticket_invoice_id', $invoiceId)->first();
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
        return $this->dataType->slug == 'ticket-invoices';
    }

    public function getDefaultRoute()
    {
        if ($this->checkReceipt()) {
            return route('admin.reports.generate.ticket-invoice-money-receipts-report', $this->data->{$this->data->getKeyName()});
        }

        return route('voyager.ticket-invoice-money-receipts.create') . '?ticket_invoice_id=' . $this->data->{$this->data->getKeyName()};
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class TicketInvoiceMoneyReceipt extends Model
{
    protected $with = ['invoice'];
    public function invoice(){
        return $this->belongsTo(TicketInvoice::class,  'ticket_invoice_id', 'id' );
    }

}

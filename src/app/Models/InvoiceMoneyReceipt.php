<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class InvoiceMoneyReceipt extends Model
{
    protected $with = ['invoice'];
    public function invoice(){
        return $this->belongsTo(Invoice::class,  'invoice_id', 'id' );
    }

}

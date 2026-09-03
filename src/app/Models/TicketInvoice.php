<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class TicketInvoice extends Model
{
    protected $with = ['ticketCompany','invoiceLines', 'agencier', 'companier', 'candidate'];

    public function ticketCompany(){
        return $this->belongsTo(TicketCompany::class,  'ticket_company_id', 'id' );
    }

    public function agencier(){
        return $this->belongsTo(Agencier::class,  'agencier_id', 'id' );
    }

    public function companier(){
        return $this->belongsTo(Companier::class,  'companier_id', 'id' );
    }

    public function invoiceLines(){
        return $this->hasMany(TicketInvoiceLine::class,  'ticket_invoice_id', 'id' );
    }

    public function candidate(){
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class TicketInvoiceLine extends Model
{

    protected $with = ['candidate'];

    public function candidate(){
        return $this->belongsTo(Candidate::class,  'candidate_id', 'id' );
    }
}

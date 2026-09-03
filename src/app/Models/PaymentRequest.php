<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class PaymentRequest extends Model
{
    public function candidate(){
        return $this->belongsTo(Candidate::class,  'candidate_id', 'id' );
    }
    public function agent(){
        return $this->belongsTo(Agent::class,  'agent_id', 'id' );
    }
}

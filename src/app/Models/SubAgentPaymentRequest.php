<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class SubAgentPaymentRequest extends Model
{
    public function candidate(){
        return $this->belongsTo(Candidate::class,  'candidate_id', 'id' );
    }
    public function subAgent(){
        return $this->belongsTo(SubAgent::class,  'sub_agent_id', 'id' );
    }
}

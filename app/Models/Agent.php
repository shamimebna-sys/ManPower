<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Agent extends Model
{
    protected $fillable = ['name'];
    public $additional_attributes = ['agent_details_info'];
    protected $with = ['payments'];

    public function getAgentDetailsInfoAttribute()
    {
        return ' ('.$this->code.') '. $this->name.' - '.$this->mobile ;
    }


    public function payments(){
        return $this->hasMany(Payment::class,  'agent_id', 'id' )->orderBy('id', 'desc');
    }
}

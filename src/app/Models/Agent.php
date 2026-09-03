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


    /*public function payments2(){
        return $this->hasMany(Payment::class,  'agent_id', 'id' )->orderBy('id', 'desc');
    }*/

    public function payments()
    {

        return  $this->hasMany(Payment::class, 'agent_id', 'id')
            ->select('*', \DB::raw("
            ROUND(
        SUM(
            CASE
                WHEN status = 'A' AND type = 'CR' THEN
                    amount / IF(currency_id = 2, 1, NULLIF(exchange_rate, 0))

                WHEN status = 'A' AND type = 'DR' THEN
                    -(amount / IF(currency_id = 2, 1, NULLIF(exchange_rate, 0)))

                ELSE 0
            END
        ) OVER (
            PARTITION BY agent_id
            ORDER BY updated_at ASC
        ),
        2
    ) AS balance
        "))
            ->orderBy('updated_at', 'desc');



            return $this->hasMany(Payment::class, 'agent_id', 'id')
            ->select('*', \DB::raw("
            SUM(
                CASE
                    WHEN type = 'CR' THEN amount
                    WHEN type = 'DR' THEN -amount
                    ELSE 0
                END
            ) OVER (
                PARTITION BY agent_id
                ORDER BY id asc
            ) as balance
        "))
            ->orderBy('id', 'asc');
    }
}

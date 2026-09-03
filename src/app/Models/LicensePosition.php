<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class LicensePosition extends Model
{
    protected $with = ['position'];
    public function position(){
        return $this->belongsTo(Position::class,  'position_id', 'id' );
    }

}

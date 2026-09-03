<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class License extends Model
{

    protected $with = ['licensePositions',  'companier'];

    public function companier(){
        return $this->belongsTo(Companier::class,  'companier_id', 'id' );
    }

    public function licensePositions(){
        return $this->hasMany(LicensePosition::class,  'license_id', 'id' );
    }

}

<?php

namespace App\Models;

use App\Helpers\CommonClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class Companier extends Model
{
    protected $with = ['country'];
    public function country(){
        return $this->belongsTo(Country::class,  'country_id', 'id' );
    }

    protected static function boot()
    {
        parent::boot();
        if (CommonClass::user() && CommonClass::user()->role_id == 108) {
            $compinerIds = Candidate::where('agencier_id', CommonClass::user()->profile_id)
                ->distinct()
                ->pluck('companier_id')
                ->toArray();

            static::addGlobalScope('id', function (Builder $builder) use ($compinerIds) {
                if (!empty($compinerIds)) {
                    $builder->whereIn('id', $compinerIds);
                } else {
                    $builder->whereRaw('0 = 1');
                }
            });
        }
    }
}

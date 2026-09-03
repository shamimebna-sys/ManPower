<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\CommonClass;
use Illuminate\Database\Eloquent\Builder;


class Notification extends Model
{
    protected static function boot()
    {
        parent::boot();
        /*if (CommonClass::user() && CommonClass::user()->role_id !=1) {
            static::addGlobalScope('id', function (Builder $builder) {
                $builder->where('id', '=', \Auth::id());
            });
        }*/
    }
}

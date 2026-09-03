<?php

namespace App\Models;

use App\Helpers\CommonClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Payment extends Model
{

    public function subAgent(){
        return $this->belongsTo(SubAgent::class,  'sub_agent_id', 'id' );
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('id', function (Builder $builder) {
            if (CommonClass::user() && in_array(CommonClass::user()->role_id, [101])) {
                if (Str::contains(request()->path(), 'my/wallet')) {
                    $builder->where('agent_id', '=', CommonClass::user()->profile_id)
                     ->where('sub_agent_id', '=', 0);
                }else{
                    if (Str::contains(request()->path(), 'payments')) {
                        $builder->whereIn('sub_agent_id',
                            SubAgent::where('agent_id', CommonClass::user()->profile_id)->pluck('id')
                        );
                    }
                }
            } elseif (CommonClass::user() && in_array(CommonClass::user()->role_id, [109])) {
                $builder->where('sub_agent_id', '=', CommonClass::user()->profile_id);
            }else{
                $builder->where('sub_agent_id', '=', 0);
            }
        });
    }

}

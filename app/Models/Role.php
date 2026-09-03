<?php

namespace App\Models;

use App\Helpers\CommonClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Tests\Database\Factories\RoleFactory;

class Role extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function users()
    {
        $userModel = Voyager::modelClass('User');

        return $this->belongsToMany($userModel, 'user_roles')
                    ->select(app($userModel)->getTable().'.*')
                    ->union($this->hasMany($userModel))->getQuery();
    }

    public function permissions()
    {
        return $this->belongsToMany(Voyager::modelClass('Permission'));
    }

    protected static function newFactory()
    {
        return RoleFactory::new();
    }


    protected static function boot()
    {
        parent::boot();
        if (CommonClass::user() && CommonClass::user()->role_id !=1) {
            static::addGlobalScope('id', function (Builder $builder) {
                $builder->where('id', '>', 1);
            });
        }
    }
}

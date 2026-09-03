<?php

namespace App\Models;

use App\Helpers\CommonClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


class Employee extends Model
{
    public $additional_attributes = ['info'];
    protected $with = [ 'payments'];

    public function getInfoAttribute()
    {
        return ' ('.$this->code.') '. $this->name;
    }

    public function payments(){
        return $this->hasMany(Payment::class,  'employee_id', 'id' )->orderBy('id', 'desc');
    }
    protected static function boot()
    {
        parent::boot();
        /*if (CommonClass::user() && CommonClass::user()->role_id !=1) {
            static::addGlobalScope('id', function (Builder $builder) {
                $builder->where('id', '=', CommonClass::user()->employee_id);
            });
        }*/
    }
}

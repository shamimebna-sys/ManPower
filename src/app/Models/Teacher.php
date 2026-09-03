<?php

namespace App\Models;

use App\Helpers\CommonClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


class Teacher extends Model
{
    public $additional_attributes = ['teacher_info'];
    protected $with = ['schedules', 'payments'];

    public function getTeacherInfoAttribute()
    {
        return ' ('.$this->code.') '. $this->name;
    }

    public function schedules(){
        return $this->hasMany(ClassSchedule::class,  'teacher_id', 'id' )->orderBy('id', 'desc');
    }

    /*public function payments(){
        return $this->hasMany(Payment::class,  'teacher_id', 'id' )->orderBy('id', 'desc');
    }*/

    public function payments()
    {
        return $this->hasMany(Payment::class, 'teacher_id', 'id')
            ->select('*', \DB::raw("
            SUM(
                CASE
                    WHEN type = 'CR' THEN amount
                    WHEN type = 'DR' THEN -amount
                    ELSE 0
                END
            ) OVER (
                PARTITION BY teacher_id
                ORDER BY id asc
            ) as balance
        "))
            ->orderBy('id', 'asc');
    }

    protected static function boot()
    {
        parent::boot();
        /*if (CommonClass::user() && CommonClass::user()->role_id !=1) {
            static::addGlobalScope('id', function (Builder $builder) {
                $builder->where('id', '=', CommonClass::user()->teacher_id);
            });
        }*/
    }
}

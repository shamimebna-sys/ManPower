<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ClassSchedule extends Model
{
    protected $with = ['classGroup'];
    public function classGroup(){
        return $this->hasMany(ClassGroup::class,  'id', 'id' )->orderBy('id', 'desc');
    }
}

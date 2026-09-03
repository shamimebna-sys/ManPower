<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ClassSchedule extends Model
{
    protected $with = ['classGroup'];
    public function classGroup(){
        return $this->hasMany(ClassGroup::class,  'id', 'id' )->orderBy('id', 'desc');
    }

    public function classGroupRelation(){
        return $this->belongsTo(ClassGroup::class, 'class_group_id', 'id');
    }

    public function teacher(){
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }
}

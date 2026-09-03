<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Exam extends Model
{
    public function classGroup(){
        return $this->belongsTo(ClassGroup::class,  'class_group_id', 'id' );
    }
}

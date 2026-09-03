<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ClassGroup extends Model
{

    public $additional_attributes = ['group_info','total_candidate' ];

    public function getGroupInfoAttribute()
    {
        if (isset($this->attributes['group_info'])) {
            return $this->attributes['group_info'];
        }
        return ' ('.$this->code.') '. $this->name;
    }


    public function getTotalCandidateAttribute()
    {
        return Candidate::where('class_group_id', $this->id)->count();
    }
}

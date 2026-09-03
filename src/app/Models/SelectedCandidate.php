<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class SelectedCandidate extends Model
{
    public $with = ['classGroup', 'candidate'];
    public function classGroup(){
        return $this->belongsTo(ClassGroup::class,  'class_group_id', 'id' );
    }

    public function candidate(){
        return $this->belongsTo(Candidate::class,  'candidate_id', 'id' );
    }

}

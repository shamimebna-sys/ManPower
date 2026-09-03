<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ExamResult extends Model
{
    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function candidateId()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class, 'class_group_id');
    }
}



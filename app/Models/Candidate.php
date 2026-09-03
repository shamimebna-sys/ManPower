<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Candidate extends Model
{
    public $additional_attributes = ['candidate_details_info'];

    public function getCandidateDetailsInfoAttribute()
    {
        return ' ('.$this->code.') '. $this->name.' - '.$this->mobile ;
    }

    public function classGroup(){
        return $this->belongsTo(ClassGroup::class,  'class_group_id', 'id' );
    }

    public function user(){
        return $this->hasOne(User::class,  'candidate_id', 'id' );
    }

    public function agency(){
        return $this->hasMany(Agency::class,  'candidate_id', 'id' );
    }
    public function company(){
        return $this->hasMany(Company::class,  'candidate_id', 'id' );
    }
    public function VisaImmigration(){
        return $this->hasMany(VisaImmigration::class,  'candidate_id', 'id' );
    }

    public function latestExamResult()
    {
        return $this->hasOne(ExamResult::class, 'candidate_id')->where('result', 'PASS')->latestOfMany();
    }

    public function employerCandidates()
    {
        return $this->hasMany(EmployerCandidate::class, 'candidate_id');
    }
}

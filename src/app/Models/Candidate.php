<?php

namespace App\Models;

use App\Helpers\CommonClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class Candidate extends Model
{
    public $additional_attributes = ['candidate_details_info'];

    public function getCandidateDetailsInfoAttribute()
    {
//        return ' ('.$this->code.') '. $this->name.', '.$this->mobile.', '.$this->passport_no ;
        return   $this->name.' ('.$this->passport_no.')' ;
    }

    public function classGroup(){
        return $this->belongsTo(ClassGroup::class,  'class_group_id', 'id' );
    }
    public function agent(){
        return $this->belongsTo(Agent::class,  'agent_id', 'id' );
    }
    public function subAgent(){
        return $this->belongsTo(SubAgent::class,  'sub_agent_id', 'id' );
    }
    public function agencier(){
        return $this->belongsTo(Agencier::class,  'agencier_id', 'id' );
    }
    public function companier(){
        return $this->belongsTo(Companier::class,  'companier_id', 'id' );
    }
    public function positionRelation(){
        return $this->belongsTo(Position::class,  'position_id', 'id' );
    }

    public function replacedByCandidate(){
        // Added for candidate replacement feature
        return $this->belongsTo(Candidate::class, 'replaced_by_candidate_id', 'id');
    }

    public function replacesCandidate(){
        // Added for candidate replacement feature
        return $this->hasOne(Candidate::class, 'replaced_by_candidate_id', 'id');
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
    public function flightSchedules(){
        return $this->hasMany(FlightSchedule::class, 'candidate_id');
    }

    public function latestExamResult()
    {
        return $this->hasOne(ExamResult::class, 'candidate_id')->where('result', 'PASS')->latestOfMany();
    }

    public function latestExamResultAny()
    {
        return $this->hasOne(ExamResult::class, 'candidate_id')->latestOfMany();
    }

    public function employerCandidates()
    {
        return $this->hasMany(EmployerCandidate::class, 'candidate_id');
    }

    protected static function boot()
    {
        parent::boot();
       // dd(CommonClass::user()->role->id);
        if (CommonClass::user() && in_array(CommonClass::user()->role_id, [101, 108, 109])) {
            static::addGlobalScope('id', function (Builder $builder) {
               // dd(CommonClass::user()->profile_id);
              //  $builder->where('agencier_id', '=', CommonClass::user()->profile_id);
                if(CommonClass::user()->role->id == 101){
                    $builder->where('agent_id', '=', CommonClass::user()->profile_id);
                }elseif(CommonClass::user()->role->id == 109){
                    $builder->where('sub_agent_id', '=', CommonClass::user()->profile_id);
                }elseif(CommonClass::user()->role->id == 108){
                    $builder->where('agencier_id', '=', CommonClass::user()->profile_id);
                }

            });
        }
    }
}

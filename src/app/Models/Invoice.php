<?php

namespace App\Models;

use App\Helpers\CommonClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


class Invoice extends Model
{
    protected $with = ['invoiceLines', 'agencier', 'companier', 'country', 'invoiceCandidates'];
    public function invoiceLines(){
        return $this->hasMany(InvoiceLine::class,  'invoice_id', 'id' );
    }

    public function agencier(){
        return $this->belongsTo(Agencier::class,  'agenciers_id', 'id' );
    }
    public function companier(){
        return $this->belongsTo(Companier::class,  'companier_id', 'id' );
    }
    public function country(){
        return $this->belongsTo(Country::class,  'country_id', 'id' );
    }

    public function invoiceCandidates(){
        return $this->belongsToMany(Candidate::class,  'invoice_candidates', 'invoice_id', 'candidate_id' );
    }

    protected static function boot()
    {
        parent::boot();
        if (CommonClass::user() && CommonClass::user()->role_id !=1) {
            static::addGlobalScope('agenciers_id', function (Builder $builder) {
                $builder->where('agenciers_id', !empty(CommonClass::user()->profile_id)?CommonClass::user()->profile_id:CommonClass::user()->agencier_id);
            });
        }
    }
}

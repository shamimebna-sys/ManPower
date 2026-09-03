<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class CandidateDocument extends Model
{
    public function documentType(){
        return $this->belongsTo(DocumentType::class,  'document_type_id', 'id' );
    }
}

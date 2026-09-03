<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class PoliceClearance extends Model
{
    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
}

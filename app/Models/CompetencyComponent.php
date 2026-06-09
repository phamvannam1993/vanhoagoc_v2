<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetencyComponent extends Model
{
    protected $table = 'competency_components';
    protected $guarded = [];

    public function competency()
    {
        return $this->belongsTo(Competency::class, 'competency_id');
    }
}

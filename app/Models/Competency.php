<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Competency extends Model
{
    protected $table = 'competencies';
    protected $guarded = [];

    public function components()
    {
        return $this->hasMany(CompetencyComponent::class, 'competency_id');
    }
}

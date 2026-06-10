<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExerciseItem extends Model
{
    use HasFactory;

    protected $fillable = ['exercise_id', 'name', 'order', 'level', 'question_mix', 'total_questions'];

    protected $casts = [
        'question_mix' => 'array',
    ];

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExerciseQuestion::class)->orderBy('order');
    }
}

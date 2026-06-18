<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExerciseItem extends Model
{
    use HasFactory;

    protected $fillable = ['exercise_id', 'name', 'order', 'level', 'question_mix', 'total_questions', 'app_id', 'book_id', 'practice_id', 'week_id'];

    protected $casts = [
        'question_mix' => 'array',
    ];

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function practice(): BelongsTo
    {
        return $this->belongsTo(Practice::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExerciseQuestion::class)->orderBy('order');
    }

    public function questionEditors(): HasMany
    {
        return $this->hasMany(QuestionEditor::class, 'exercise_item_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    use HasFactory;

    protected $fillable = ['lesson_draft_id', 'title', 'description', 'total_questions', 'question_mix'];

    protected $casts = [
        'question_mix' => 'array',
    ];

    public function lessonDraft(): BelongsTo
    {
        return $this->belongsTo(LessonDraft::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExerciseItem::class)->orderBy('order');
    }
}

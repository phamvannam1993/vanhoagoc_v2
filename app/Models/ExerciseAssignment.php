<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExerciseAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['exercise_item_id', 'class_id', 'class_code', 'due_date', 'due_time', 'note', 'status'];

    public function exerciseItem(): BelongsTo
    {
        return $this->belongsTo(ExerciseItem::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(AssignmentStudent::class);
    }
}

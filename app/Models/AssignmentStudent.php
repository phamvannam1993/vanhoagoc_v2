<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentStudent extends Model
{
    use HasFactory;

    protected $fillable = ['exercise_assignment_id', 'student_id', 'status', 'submission_file', 'score'];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ExerciseAssignment::class, 'exercise_assignment_id');
    }
}

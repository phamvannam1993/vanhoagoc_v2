<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonDraft extends Model
{
    protected $table = 'lesson_drafts';

    protected $fillable = [
        'practice_id',
        'app_id',
        'book_id',
        'week_id',
        'lesson_doc',
        'lesson_noi',
        'lesson_video',
        'type',
        'status',
        'created_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function practice()
    {
        return $this->belongsTo(Practice::class, 'practice_id');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByPractice($query, $practiceId)
    {
        return $query->where('practice_id', $practiceId);
    }
}

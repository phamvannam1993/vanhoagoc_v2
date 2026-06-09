<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Practice extends Model
{
    use HasFactory;

    protected $table = "practice";

    protected $guarded = [];
//    protected $appends = ['assigned_students_count'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function questionEditors()
    {
        return $this->hasMany(QuestionEditor::class);
    }

    public function assignedForUser()
    {
        return $this->hasMany(PracticeClass::class, 'practice_id');
    }

    public function week()
    {
        return $this->belongsTo(Week::class, 'week_id');
    }

    public function book()
    {
        return $this->hasOneThrough(Book::class, Week::class, 'id', 'id', 'week_id', 'book_id');
    }

    public function assignedClasses()
    {
        return $this->hasMany(PracticeClass::class, 'practice_id');
    }

    public function points()
    {
        return $this->hasMany(Point::class, 'practice_id');
    }

    public function practiceClasses()
    {
        return $this->hasMany(PracticeClass::class, 'practice_id');
    }

    public function practicePoints()
    {
        return $this->hasMany(Point::class);
    }

    public function userRankPractices()
    {
        return $this->hasMany(UserRankPractice::class);
    }

    public function practiceImages()
    {
        return $this->hasMany(PracticeImage::class);
    }
//    public function getAssignedStudentsCountAttribute()
//    {
//        // Lấy tất cả class_id mà giáo viên này giao bài (qua practice_classes)
//        $classIds = $this->practiceClasses->pluck('class_id')->unique();
//
//        // Đếm học sinh thuộc các lớp đó
//        return \App\Models\UserClass::whereIn('class_id', $classIds)
//            ->distinct('user_id')
//            ->count('user_id');
//    }
}

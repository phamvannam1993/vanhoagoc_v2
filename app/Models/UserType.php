<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    use HasFactory;

    protected $table = "user_type";

    const TYPE_ADMIN= 'admin';
    const TYPE_EDITOR= 'editor';
    const TYPE_DIRECTOR = 'director';
    const TYPE_TEACHER = 'teacher';
    const TYPE_TEACHER_ADMIN = 'teacher';
    const TYPE_STUDENT = 'student';
    const TYPE_STUDENT_APP = 'student_app';
    protected $guarded = [];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserClass extends Model
{
    use HasFactory;

    protected $table = 'users_classes';

    protected $guarded = [];

    public function practiceClass()
    {
        return $this->hasMany(PracticeClass::class, 'class_id', 'class_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function classes()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
}

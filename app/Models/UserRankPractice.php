<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRankPractice extends Model
{
    use HasFactory;
    protected $table = "user_rank_practices";
    protected $guarded = [];

    public function practice()
    {
        return $this->belongsTo(Practice::class, 'practice_id');
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

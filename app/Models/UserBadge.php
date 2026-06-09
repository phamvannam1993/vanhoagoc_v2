<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBadge extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function badge()
    {
        return $this->belongsTo(Badge::class, 'badge_id');
    }
}

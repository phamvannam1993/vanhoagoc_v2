<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealtimeEvent extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function eventPoints()
    {
        return $this->hasMany(RealtimeEventPoint::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function realtimeEventUsers()
    {
        return $this->hasMany(RealtimeEventUser::class);
    }
}

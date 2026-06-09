<?php

namespace App\Models;

use App\Enums\RealtimeEventUserConstant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealtimeEventUser extends Model
{
    use HasFactory;

    protected $table = 'realtime_event_user';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    ///////////////////
    public function getStatusLabel()
    {
        return RealtimeEventUserConstant::status()[$this->status];
    }
}

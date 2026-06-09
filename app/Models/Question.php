<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $table = 'question';

    protected $casts = [
        'created_at' => 'datetime:d/m/Y H:i',
    ];

    protected $guarded = [];

    protected $appends = ['beauty_time_display'];

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function getBeautyTimeDisplayAttribute()
    {
        $seconds = $this->time_display;

        if ($seconds <= 0) {
            return '';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes == 0) {
            return "{$remainingSeconds} giây";
        }

        return "{$minutes} phút" . ($remainingSeconds > 0 ? " {$remainingSeconds} giây" : '');
        }
}

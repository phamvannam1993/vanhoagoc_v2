<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    use HasFactory;

    protected $guarded = [];
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d/m/Y');
    }
    public function point_detais()
    {
        return $this->hasMany(PointDetail::class, 'point_id');
    }

    public function pointDetails()
    {
        return $this->hasMany(PointDetail::class, 'point_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function practiceInfos()
    {
        return $this->hasMany(PracticeClass::class, 'practice_id', 'practice_id');
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }
}

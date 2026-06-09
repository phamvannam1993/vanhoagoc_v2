<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:d/m/Y H:i',
    ];

    protected $appends = ['beauty_created_at'];

    public function app()
    {
        return $this->belongsTo(App::class, 'app_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, UserClass::class, 'class_id', 'id', 'id', 'user_id');
    }

    public function practiceClass()
    {
        return $this->hasMany(PracticeClass::class, 'class_id', 'id');
    }

    public function point()
    {
        return $this->hasMany(Point::class, 'class_id', 'id');
    }

    ////////////////////////////
    public function getBeautyCreatedAtAttribute()
    {
        return \Carbon\Carbon::parse($this->created_at)->format('H:i d/m/Y');
    }
}

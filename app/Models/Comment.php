<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:d/m/Y H:i',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function app()
    {
        return $this->belongsTo(App::class, 'app_id');
    }
}

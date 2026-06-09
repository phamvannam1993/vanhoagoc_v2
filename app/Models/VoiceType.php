<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoiceType extends Model
{
    use HasFactory;

    protected $table = "voice_types";

    protected $fillable = ['name', 'type'];
    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
    ];

    protected $guarded = [];
}

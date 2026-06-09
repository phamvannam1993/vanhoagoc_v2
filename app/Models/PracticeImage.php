<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PracticeImage extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['full_url'];

    public function getFullUrlAttribute()
    {
        if (!$this->path) return null;

        return Storage::disk(config('filesystems.storage_disk'))->url($this->path);
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }
}

<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionTemplate extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'template' => 'array',
        'created_at' => 'datetime:d/m/Y H:i',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image ? Helper::getCloudFront($this->image) : null;
    }
}

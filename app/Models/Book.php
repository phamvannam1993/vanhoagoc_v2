<?php

namespace App\Models;

use App\Casts\ImagePath;
use App\Enums\BookConstant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $table = 'book';

    protected $guarded = [];

    public function weeks()
    {
        return $this->hasMany(Week::class, 'book_id', 'id');
    }

    public function app()
    {
        return $this->belongsTo(App::class);
    }
}

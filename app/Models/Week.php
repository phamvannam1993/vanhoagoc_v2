<?php

namespace App\Models;

use App\Casts\ImagePath;
use App\Enums\WeekConstant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

class Week extends Model
{
    use HasFactory;

    protected $table = "week";

    protected $guarded = [];

    public function practices()
    {
        return $this->hasMany(Practice::class, 'week_id', 'id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}

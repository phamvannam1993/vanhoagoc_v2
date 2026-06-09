<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    use HasFactory;

    protected $table = 'app';

    protected $guarded = [];

    public function classes()
    {
        return $this->hasMany(Classes::class);
    }

    public function books()
    {
        return $this->hasMany(Book::class);
    }
}

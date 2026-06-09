<?php

namespace App\Models;

use App\Enums\UserAccessModuleConstant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAccessModule extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function app()
    {
        return $this->belongsTo(App::class, 'module_id'); //Với module_type là app
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'module_id'); //Với module_type là book
    }

    public function week()
    {
        return $this->belongsTo(Week::class, 'module_id'); //Với module_type là week
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProcess extends Model
{
    use HasFactory;

    protected $table = 'user_process';

    protected $guarded = [];
}

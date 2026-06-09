<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function targetItems()
    {
        return $this->hasMany(TargetItem::class, 'target_id');
    }
}

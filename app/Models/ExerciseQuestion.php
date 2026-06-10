<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'exercise_item_id', 'kind', 'tieu_de', 'options', 'cot_a', 'cot_b',
        'dap_an_dung', 'muc_do', 'trich_dan_dap_an', 'order'
    ];

    protected $casts = [
        'options' => 'array',
        'cot_a' => 'array',
        'cot_b' => 'array',
    ];

    public function exerciseItem(): BelongsTo
    {
        return $this->belongsTo(ExerciseItem::class);
    }
}

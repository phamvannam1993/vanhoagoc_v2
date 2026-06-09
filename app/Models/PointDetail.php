<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function questionEditor()
    {
        return $this->belongsTo(QuestionEditor::class, 'question_id');
    }
}

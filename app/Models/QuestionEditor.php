<?php

namespace App\Models;

use App\Helpers\Helper;
use App\Helpers\MediaHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionEditor extends Model
{
    use HasFactory;

    protected $table = 'question_editor';

    protected $guarded = [];

    protected $casts = [
        'answers' => 'array',
        'answer_connects' => 'array'
    ];

    public function getRealAnswer($type, $val)
    {
        return $this->getCorrectPath($type, $val);
    }

    public function getRealAnswerConnect($type, $val)
    {
        return $this->getCorrectPath($type, $val);
    }

    public function getCorrectPath($type, $value)
    {
        if (empty($value)) {
            return '';
        }

        $result = '';
        switch ($type) {
            case 'text':
                $result = $value;
                break;
            case 'image':
            case 'audio':
            case 'video':
                $result = Helper::getCloudFront($value);
                break;
        }

        return $result;
    }

    public function templateQuestion()
    {
        return $this->belongsTo(QuestionTemplate::class, 'template', 'id');
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }

    public function competency()
    {
        return $this->belongsTo(Competency::class, 'competency_id');
    }

    public function competencyComponent()
    {
        return $this->belongsTo(CompetencyComponent::class, 'competency_component_id');
    }

    public function educationalContent()
    {
        return $this->belongsTo(EducationalContent::class, 'educational_content_id');
    }
}

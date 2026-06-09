<?php

namespace App\Repositories;

use App\Models\Answer;

class AnswerRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Answer::class;
    }
}

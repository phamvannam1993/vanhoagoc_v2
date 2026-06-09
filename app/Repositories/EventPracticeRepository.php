<?php

namespace App\Repositories;

use App\Models\EventPractice;

class EventPracticeRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return EventPractice::class;
    }
}

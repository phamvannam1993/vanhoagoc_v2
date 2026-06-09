<?php

namespace App\Repositories;

use App\Models\Task;

class TaskRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Task::class;
    }
}

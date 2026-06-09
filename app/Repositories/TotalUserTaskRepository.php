<?php

namespace App\Repositories;

use App\Models\TotalUserTask;

class TotalUserTaskRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return TotalUserTask::class;
    }
}

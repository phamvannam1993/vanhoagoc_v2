<?php

namespace App\Repositories;

use App\Models\Log;

class LogRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Log::class;
    }
}

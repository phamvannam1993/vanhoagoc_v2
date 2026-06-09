<?php

namespace App\Repositories;

use App\Models\Target;

class TargetRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Target::class;
    }
}

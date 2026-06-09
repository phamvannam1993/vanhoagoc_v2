<?php

namespace App\Repositories;

use App\Models\HumanResource;

class HumanResourceRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return HumanResource::class;
    }
}

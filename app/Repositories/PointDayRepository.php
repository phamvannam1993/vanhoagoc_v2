<?php

namespace App\Repositories;

use App\Models\PointDay;

class PointDayRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return PointDay::class;
    }
}

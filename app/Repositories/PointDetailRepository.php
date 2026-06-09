<?php

namespace App\Repositories;

use App\Models\PointDetail;

class PointDetailRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return PointDetail::class;
    }
}

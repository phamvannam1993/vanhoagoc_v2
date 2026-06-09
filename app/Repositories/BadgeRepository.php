<?php

namespace App\Repositories;

use App\Models\Badge;

class BadgeRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Badge::class;
    }
}

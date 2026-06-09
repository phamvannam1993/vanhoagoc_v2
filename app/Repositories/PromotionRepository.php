<?php

namespace App\Repositories;

use App\Models\Promotion;

class PromotionRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Promotion::class;
    }
}

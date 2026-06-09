<?php

namespace App\Repositories;

use App\Models\TargetItem;

class TargetItemRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return TargetItem::class;
    }
}

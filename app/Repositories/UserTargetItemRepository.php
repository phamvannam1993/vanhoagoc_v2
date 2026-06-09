<?php

namespace App\Repositories;

use App\Models\UserTargetItem;

class UserTargetItemRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return UserTargetItem::class;
    }
}

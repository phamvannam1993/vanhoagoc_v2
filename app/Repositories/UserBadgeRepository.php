<?php

namespace App\Repositories;

use App\Models\UserBadge;

class UserBadgeRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return UserBadge::class;
    }
}

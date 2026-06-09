<?php

namespace App\Repositories;

use App\Models\UserInfo;

class UserInfoRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return UserInfo::class;
    }
}

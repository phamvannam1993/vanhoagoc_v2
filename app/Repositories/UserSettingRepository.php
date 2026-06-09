<?php

namespace App\Repositories;

use App\Models\UserSetting;

class UserSettingRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return UserSetting::class;
    }
}

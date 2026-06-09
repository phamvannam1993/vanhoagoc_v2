<?php

namespace App\Repositories;

use App\Enums\UserTypeConstant;
use App\Models\UserType;

class UserTypeRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return UserType::class;
    }

    public function getList()
    {
        $query = $this->model->where('status', UserTypeConstant::ON)
            ->where('type', '!=', \App\Models\UserType::TYPE_DIRECTOR);

        return $query->paginate(20);
    }
}

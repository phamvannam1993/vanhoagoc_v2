<?php

namespace App\Repositories;

use App\Models\Role;
class RoleRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Role::class;
    }

    public function getAppByUser($userId)
    {
        return $this->model->where('user_id', $userId)->first();
    }

    public function updateOrCreateRoleUser(array $data)
    {
        return $this->model->updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'permission' => $data['app_id'],
            ],
            [
                'user_type_id' => $data['type_id'],
            ]
        );
    }
}

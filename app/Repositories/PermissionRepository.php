<?php

namespace App\Repositories;

use App\Models\Permission;

class PermissionRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Permission::class;
    }
}

<?php

namespace App\Repositories;

use App\Models\UserClass;

class UserClassRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return UserClass::class;
    }

    public function getStudentByClass($classIds)
    {
        return $this->model->whereIn('class_id', $classIds);
    }
}

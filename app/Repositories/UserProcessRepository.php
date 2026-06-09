<?php

namespace App\Repositories;

use App\Models\UserProcess;

class UserProcessRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return UserProcess::class;
    }

    public function getList($params)
    {
        $query = $this->model->query();

        if (!empty($params['user_id'])) {
            $query = $query->where('user_id', $params['user_id']);
        }

        if (!empty($params['practice_id'])) {
            $query = $query->where('practice_id', $params['practice_id']);
        }
       
        $query->orderBy('id', 'DESC');

        return $query->get();
    }
}

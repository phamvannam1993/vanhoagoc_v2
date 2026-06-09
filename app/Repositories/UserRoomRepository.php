<?php

namespace App\Repositories;

use App\Models\UserRoom;

class UserRoomRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return UserRoom::class;
    }

    public function getList($data)
    {
        return $this->model->where('user_id', $data['user_id'])->get();
    }

    public function create($data)
    {
        return $this->model->create($data);
    }
}

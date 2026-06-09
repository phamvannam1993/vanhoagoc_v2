<?php

namespace App\Repositories;

use App\Models\UserTotalPoint;

class UserTotalPointRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return UserTotalPoint::class;
    }

    public function getList($data)
    {
        $query = $this->model->query();

        if (!empty($data['type'])) {
            $query = $query->where('user_total_points.type', $data['type']);
        }

        if (!empty($data['user_id'])) {
            $query = $query->where('user_total_points.user_id', $data['user_id']);
        }

        $list = $query->join('users', 'users.id', '=', 'user_total_points.user_id')
            ->with('user')
            ->orderBy('user_total_points.total_star_count', 'desc')
            ->orderBy('user_total_points.total_time', 'desc')
            ->get();

        return $list;
    }
}

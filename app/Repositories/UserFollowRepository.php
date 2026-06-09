<?php

namespace App\Repositories;

use App\Models\UserFollow;

class UserFollowRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return UserFollow::class;
    }

    public function getTotalByType($userId, $type = 1)
    {
        $query = $this->model->query();

        if ($type == 1) {
            $query = $query->where('user_id', $userId);
        } else {
            $query = $query->where('user_follow_id', $userId);
        }

        return $query->count();
    }

    public function getUserFollow($params = [])
    {
        $query = $this->model->query();

        if (!empty($params['user_id'])) {
            $query = $query->where('user_id', $params['user_id']);
            $query = $query->with('userFollow');
        }

        if (!empty($params['user_follow_id'])) {
            $query = $query->where('user_follow_id', $params['user_follow_id']);
            $query = $query->with('user');
        }

        return $query->first();
    }

    public function getListUserFollow($params = [])
    {
        $query = $this->model->query();

        if (!empty($params['user_id'])) {
            $query = $query->where('user_id', $params['user_id']);
            $query = $query->with('userFollow');
        }

        if (!empty($params['user_follow_id'])) {
            $query = $query->where('user_follow_id', $params['user_follow_id']);
            $query = $query->with('user');
        }

        return $query->get();
    }
    // Số lượng người mà user đang theo dõi
    public function countFollowingByUserId($userId)
    {
        return $this->model->where('user_id', $userId)->count();
    }

    // Số lượng người đang theo dõi user
    public function countFollowersByUserId($userId)
    {
        return $this->model->where('user_follow_id', $userId)->count();
    }

    public function isFollowing($userTargetId, $userIdFollow)
    {
        return $this->model->where('user_id', $userIdFollow)->where('user_follow_id', $userTargetId)->exists();
    }
}

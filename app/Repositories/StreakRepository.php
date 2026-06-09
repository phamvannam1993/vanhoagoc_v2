<?php

namespace App\Repositories;

use App\Models\Streak;

class StreakRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Streak::class;
    }

    public function getByUser($params)
    {
        $streak = $this->select();

        if (!empty($params['user_id'])) {
            $streak->where('user_id', $params['user_id']);
        }

        $streak->orderBy('start_date', 'DESC');

        return $streak->get();
    }

    public function getHighestStreakByUserId($userId)
    {
        $query = $this->select();
        $query->where('user_id', $userId);
        $query->orderBy('day_count', 'DESC');
        return $query->first();
    }

    public function getCurrentStreakByUserId($userId)
    {
        $query = $this->select();
        $query->where('user_id', $userId)
        ->whereBetween('current_date', [now()->subDay()->toDateString(), now()->toDateString()]);
        return $query->first();
    }
}

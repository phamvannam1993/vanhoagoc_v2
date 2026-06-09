<?php

namespace App\Repositories;

use App\Models\PracticePointDay;

class PracticePointDayRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return PracticePointDay::class;
    }

    public function getPracticePointDay($params = [], $isFirst = false)
    {
        $query = $this->model;

        if (!empty($params['user_id'])) {
            $query = $query->where('user_id', $params['user_id']);
        }

        if (!empty($params['day'])) {
            $query = $query->where('day', $params['day']);
        }


        if (isset($params['week']) && $params['week'] > 0) {
            $query = $query->where('week', $params['week']);
        }

        if (!empty($isFirst)) {
            return $query->first();
        }

        return $query->orderBy('day', 'asc')->get();
    }
}

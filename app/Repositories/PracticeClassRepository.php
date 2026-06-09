<?php

namespace App\Repositories;

use App\Models\PracticeClass;

class PracticeClassRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return PracticeClass::class;
    }

    public function getListByUserId($userId, $withData = [])
    {
        $query = $this->model->query();
        $query = $query->where('user_id', $userId);
        if (!empty($withData)) {
            $query->with($withData);
        }
        if(isset($filters['pageSize']) && !empty($filters['pageSize'])) {
            return $query->paginate($filters['pageSize']);
        } else {
            return $query->paginate(self::pagingItem);
        }
    }
}

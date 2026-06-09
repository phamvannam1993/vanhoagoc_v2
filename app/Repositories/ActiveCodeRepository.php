<?php

namespace App\Repositories;

use App\Models\ActiveCode;

class ActiveCodeRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return ActiveCode::class;
    }

    public function searchByFilters($params = [])
    {
        $query = $this->model->query();

        if($params['search'] && !empty($params['search'])) {
            $query->where('code', 'LIKE', "%{$params['search']}%");
        }
        $query->orderBy('id', 'DESC');

        if(isset($params['pageSize']) && !empty($params['pageSize'])) {
            return $query->paginate($params['pageSize']);
        } else {
            return $query->paginate(self::pagingItem);
        }
    }
}

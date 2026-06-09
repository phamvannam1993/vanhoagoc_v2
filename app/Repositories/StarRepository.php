<?php

namespace App\Repositories;

use App\Models\Star;

class StarRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Star::class;
    }

    public function getDetail($params)
    {
        $query = $this->model->query();

        if (!empty($params['search'])) {
            $search = $params['search'];
            $query = $query->where(function ($query) use ($search) {
                $query = $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
                if (is_numeric($search)) {
                    $query = $query->orWhere('id', $search);
                }
                return $query;
            });
        }

        if (!empty($params['user_id'])) {
            $query =  $query->where('user_id', $params['user_id']);
        }

        return $query->first();
    }
}

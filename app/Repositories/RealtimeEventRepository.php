<?php

namespace App\Repositories;

use App\Models\RealtimeEvent;

class RealtimeEventRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return RealtimeEvent::class;
    }

    public function getListPaginate($params)
    {
        $q = $this->select();

        if (!empty($params['search'])) {
            $q->where('name', 'LIKE', "%{$params['search']}%");
        }

        return $q->paginate(20);
    }

    public function getListApi($params = [])
    {
        $query = $this->getModel()->with(['realtimeEventUsers.user']);

        if (!empty($params['user_id'])) {
            $query->whereHas('realtimeEventUsers', function ($query) use ($params) {
                $query->where('user_id', $params['user_id']);
            });
        }

        if (!empty($params['per_page']) && !empty($params['page'])) {
            $perPage = (int) $params['per_page'];
            $page = (int) $params['page'];

            $offset = ($page - 1) * $perPage;

            $query->limit($perPage)->offset($offset);
        }

        return $query->orderBy('created_at', 'DESC')->get();
    }

    public function getDetailApi($id)
    {
        return $this->with(['realtimeEventUsers.user'])->where('id', $id)->first();
    }

    public function findBySourceId($sourceId)
    {
        return $this->where('source_id', $sourceId)->first();
    }
}

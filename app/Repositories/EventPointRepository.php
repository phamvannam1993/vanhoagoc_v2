<?php

namespace App\Repositories;

use App\Models\EventPoint;

class EventPointRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return EventPoint::class;
    }

    public function getRanking($params, $paging = false, $withData = [], $order = [])
    {
        $perPage = data_get($params, 'per_page', 20);
        $query = $this->getModel()->select();

        $query->with('user')->where('event_id', $params['event_id']);

        if(!empty($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }

        if(!empty($params['limit'])) {
            $query->limit(data_get($params, 'limit'));
        }

        if (!empty($params['per_page']) && !empty($params['page'])) {
            $page = (int) $params['page'];

            $offset = ($page - 1) * $perPage;

            $query->limit($perPage)->offset($offset);
        }
        $query->with($withData);
        if (!empty($order['order_by']) && !empty($order['order_type'])) {
            $query->orderBy($order['order_by'], $order['order_type']);
        }else {
            $query->orderBy('star_count', 'DESC')->orderBy('time', 'ASC')->orderBy('time_number', 'ASC');
        }

        if ($paging) {
            return $query->paginate($perPage);
        }else {
            return $query->get();
        }
    }

    public function getHighestRankByUserId($userId)
    {
        $query = $this->getModel()->select()
            ->where('user_id', $userId)
            ->whereNotNull('rank')
            ->orderBy('rank', 'ASC');

        return $query->first();
    }
}

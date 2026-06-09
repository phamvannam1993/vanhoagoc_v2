<?php

namespace App\Repositories;

use App\Enums\PointConstant;
use App\Models\Point;

class PointRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Point::class;
    }

    public function findByFilter($filters, $withData = [])
    {
        $query = $this->model->where($filters);

        if (!empty($filters['practice_id'])) {
            $query->where('practice_id', $filters['practice_id']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($withData)) {
            $query->with($withData);
        }
        $query->orderBy('created_at', 'ASC');
        return $query->first();
    }

    public function getListByType($data)
    {
        $query = $this->model->query();

        if (!empty($data['user_id'])) {
            $query->where('user_id', $data['user_id']);
        }

        if (!empty($data['book_id'])) {
            $query->where('book_id', $data['book_id']);
        }

        if (!empty($data['type'])) {
            $query->where('type', $data['type']);

            if ($data['type'] == PointConstant::POINT_PRACTICE) {
                $query = $query->with('point_detais');
            } else {
                $query = $query->with(['user' => function ($query) {
                    $query->select('id', 'point_knowledge', 'point_deepview');
                }]);
            }
        }

        return $query->get();
    }

    public function getSummary($data)
    {
        $query = $this->model->selectRaw("
            SUM(time) as total_time,
            SUM(star_count) as total_star_count
        ")
            ->groupBy('user_id');

        if (!empty($data['user_id'])) {
            $query->where('user_id', $data['user_id']);
        }

        if (!empty($data['type'])) {
            $query->where('type', $data['type']);
        }

        return $query->first();
    }

    public function getListSummary($data)
    {
        $query = $this->model->query();

        if (!empty($data['type'])) {
            $query->where('type', $data['type']);
        }

        $query->with([
            'user' => function ($q) {
                $q->selectRaw("
                    id
                    , COALESCE(name, email) as student_name
                ");
            }
        ]);

        return $query->paginate(self::pagingItem);
    }

    public function findByUser($userId, $withData = [])
    {
        $query = $this->select();
        $query->where('user_id', $userId);
        if (!empty($withData)) {
            $query->with($withData);
        }
        return $query->get();
    }
}

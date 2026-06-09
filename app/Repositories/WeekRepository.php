<?php

namespace App\Repositories;

use App\Models\Week;

class WeekRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Week::class;
    }

    public function getListByFilters($filters, $accessWeekIds = [])
    {
        $query = $this->model->query();

        if (!empty($filters['book_id'])) {
            $query->where('week.book_id', $filters['book_id']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($accessWeekIds)) {
            $query->whereIn('week.id', $accessWeekIds);
        }

        $query = $query->orderBy('sort_number', 'ASC');

        return $query->paginate(self::pagingItem);
    }

    public function getPrevById($recordTo)
    {
        return $this->model->where('sort_number', '<', $recordTo->sort_number)
            ->where('book_id', $recordTo->book_id)
            ->orderBy('sort_number', 'desc')
            ->first();
    }

    public function getNextById($recordTo)
    {
        return $this->model->where('sort_number', '>', $recordTo->sort_number)
            ->where('book_id', $recordTo->book_id)
            ->orderBy('sort_number', 'asc')
            ->first();
    }
}

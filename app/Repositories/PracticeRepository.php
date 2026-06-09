<?php

namespace App\Repositories;

use App\Models\Practice;

class PracticeRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Practice::class;
    }

    public function getListByFilters($filters, $getAll = false, $withs = [])
    {
        $query = $this->model->query();

        if (!empty($filters['week_id'])) {
            $query->where('practice.week_id', $filters['week_id']);
        }

        if (!empty($filters['book_id'])) {
            $query->where('practice.book_id', $filters['book_id']);
        }

        if (!empty($filters['search'])) {
            $query->where('practice.name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($withs)) {
            foreach ($withs as $with) {
                $query->with($with);
            }
        }

        if (!empty($filters['sort_by']) && !empty($filters['sort_type'])) {
            $query = $query->orderBy($filters['sort_by'], $filters['sort_type']);
        }

        if (empty($getAll)) {
            $query = $query->orderBy('sort_number', 'ASC');

            return $query->paginate($filters['pageSize']);
        } else {
            return $query->get();
        }
    }

    public function getSortOrder($bookId, $weekId)
    {
        $record = $this->model->where('practice.book_id', $bookId)
            ->where('practice.week_id', $weekId)
            ->orderBy("practice.sort_order", "DESC")
            ->first();

        return !empty($record->sort_order) ? ($record->sort_order + 1) : 1;
    }

    public function getPrevById($recordTo)
    {
        return $this->model->where('sort_number', '<', $recordTo->sort_number)
            ->where('book_id', $recordTo->book_id)
            ->where('week_id', $recordTo->week_id)
            ->orderBy('sort_number', 'desc')
            ->first();
    }

    public function getNextById($recordTo)
    {
        return $this->model->where('sort_number', '>', $recordTo->sort_number)
            ->where('book_id', $recordTo->book_id)
            ->where('week_id', $recordTo->week_id)
            ->orderBy('sort_number', 'asc')
            ->first();
    }

    public function getMiddleRecords($smallRecord, $bigRecord)
    {
        return $this->where('book_id', $smallRecord['book_id'])
            ->where('week_id', $smallRecord['week_id'])
            ->whereNotIn('id', [$smallRecord['id'], $bigRecord['id']])
            ->where('sort_number', '>', $smallRecord['sort_number']) //Do sort_number đang bị lỗi trùng nhau
            ->where('sort_number', '<', $bigRecord['sort_number']) //Do sort_number đang bị lỗi trùng nhau
            ->orderBy('sort_number', 'asc')->get();
    }
}

<?php

namespace App\Repositories;

use App\Enums\PointConstant;
use App\Models\Book;

class BookRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Book::class;
    }

    public function searchByFilters($filters, $accessBookIds = [], $isAdmin = false)
    {
        $query = $this->model->query();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('book.name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('book.title', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['app_id'])) {
            $query->where('book.app_id', $filters['app_id']);
        }

        if (!$isAdmin) {
            $query->whereIn('book.id', !empty($accessBookIds) ? $accessBookIds : [0]);
        }

        if (!empty($filters['bo_sach'])) {
            $query->where('book.bo_sach', $filters['bo_sach']);
        }

        $query = $query->orderBy('sort_number', 'ASC');
        return $query->paginate(self::pagingItem);
    }

    public function getPrevById($recordTo)
    {
        return $this->model->where('sort_number', '<', $recordTo->sort_number)
            ->where('app_id', $recordTo->app_id)
            ->orderBy('sort_number', 'desc')
            ->first();
    }

    public function getNextById($recordTo)
    {
        return $this->model->where('sort_number', '>', $recordTo->sort_number)
            ->where('app_id', $recordTo->app_id)
            ->orderBy('sort_number', 'asc')
            ->first();
    }

    public function getLessonByApp($appId, $userId, $classId)
    {
        return $this->model->where('app_id', $appId)->with([
            'weeks.practices' => function ($q) use($userId, $classId) {
                $q->with([
                    'practiceClasses' => function ($q1) use ($userId, $classId) {
                        $q1->where('user_id', $userId)->where('class_id', $classId)->with(['users']);
                    },

                    'practicePoints' => function ($q2) use ($classId) {
                        $q2->select('user_id', 'practice_id', 'class_id')
                            ->where('type', PointConstant::POINT_PRACTICE)
                            ->where('is_assign', PointConstant::IS_ASSIGN)
                            ->where('class_id', $classId)
                            ->distinct('user_id');
                    },
                ]);
            },
            'weeks.practices.assignedForUser' => function ($q) use ($userId, $classId) {
                $q->where('class_id', $classId);
            }
        ])->paginate(self::pagingItem);
    }
}

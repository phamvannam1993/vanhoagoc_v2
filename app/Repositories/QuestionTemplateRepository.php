<?php

namespace App\Repositories;

use App\Models\QuestionTemplate;

class QuestionTemplateRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return QuestionTemplate::class;
    }

    public function getListByFilters($filters)
    {
        $query = $this->model->query();

        if (!empty($filters['app_id'])) {
            $query->where('app_id',  $filters['app_id']);
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $search = $filters['search'];
            $query->where(function($q) use ($searchTerm, $search) {
                // Search by template name (JSON field)
                $q->whereJsonContains('template->name', $search)
                // OR search by template code (playable field)
                ->orWhere('playable', 'like', $searchTerm)
                // OR search by the template field itself
                ->orWhere('template', 'like', $searchTerm);
            });
        }

        $query = $query->orderBy('sort_number', 'ASC');
        $pageSize = isset($filters['pageSize']) ? $filters['pageSize'] : self::pagingItem;
        return $query->paginate($pageSize);
    }

    public function getByApp($appId)
    {
        return $this->model->where('app_id', $appId)->get();
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
}

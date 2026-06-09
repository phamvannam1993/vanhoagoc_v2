<?php

namespace App\Repositories;

use App\Enums\FilterDateConstant;
use App\Models\Comment;
use Carbon\Carbon;

class CommentRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Comment::class;
    }

    public function searchByFilters($filters)
    {
        $query = $this->model->query();
        $query->with(['user', 'app']);
        if (!empty($filters['app_id'])) {
            $query->where('app_id', $filters['app_id']);
        }

        if (!empty($filters['optionFilter']) && $filters['optionFilter'] > 0) {
            $optionFilter = $filters['optionFilter'];
            $now = Carbon::now()->format('Y/m/d H:i');
            $filter = FilterDateConstant::getValueOption()[$optionFilter];
            $query->whereBetween('created_at', [$filter, $now]);
        }

        if (!empty($filters['weekOptionFilter'])){
            $query->where('link', 'LIKE', '%'.urlencode($filters['weekOptionFilter']).'%');
        } elseif (!empty($filters['allowedWeekIds']) && is_array($filters['allowedWeekIds'])) {
            $weekIds = $filters['allowedWeekIds'];
            $query->where(function ($q) use ($weekIds) {
                foreach ($weekIds as $wid) {
                    $q->orWhere('link', 'LIKE', '%' . urlencode($wid) . '%');
                }
            });
        }

        if (!empty($filters['status'])){
            $query->where('status', $filters['status']);
        }

        $query->orderBy('created_at', 'DESC');

        if(isset($filters['pageSize']) && !empty($filters['pageSize'])) {
            return $query->paginate($filters['pageSize']);
        } else {
            return $query->paginate(self::pagingItem);
        }
    }
}

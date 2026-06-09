<?php

namespace App\Repositories;

use App\Models\Classes;
use App\Models\UserType;
use Illuminate\Support\Facades\Auth;

class ClassesRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Classes::class;
    }

    public function searchByFilters($filters, $withData = [], $withCountData = [])
    {
        $user = Auth::user();
        $query = $this->model->query();
        $query = $query
        ->with([
            'practiceClass' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            },
        ])
        ->withCount('point as points_count');

        if (!empty($withData)) {
            $query->with($withData);
        }

        if (!empty($withCountData)) {
            $query->withCount($withCountData);
        }

        if (!empty($filters['app_id'])) {
            $query->where('app_id', $filters['app_id']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

         if($user->userType->type === UserType::TYPE_TEACHER) {
             $query->where('user_id', $user->id);
         }

        return $query->paginate(self::pagingItem);
    }

    public function getClassByApp($appId)
    {
        return $this->model->where('app_id', $appId)->get();
    }
}

<?php

namespace App\Repositories;

use App\Enums\EventConstant;
use App\Models\Event;
use App\Models\Classes;
use App\Models\Role;
use App\Models\UserType;
use Illuminate\Support\Facades\Auth;

class EventRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Event::class;
    }

    public function getListPaginate($params)
    {
        $q = $this->select()->with('class:id,name');

        if (!empty($params['search'])) {
            $q->where('name', 'LIKE', "%{$params['search']}%");
        }

        $user = Auth::user();
        if ($user && $user->userType) {
            $type = $user->userType->type;

            if ($type === UserType::TYPE_TEACHER) {
                $managedClassIds = Classes::where('user_id', $user->id)
                    ->pluck('id')
                    ->toArray();

                $q->where(function ($query) use ($user, $managedClassIds) {
                    $query->where('create_id', $user->id)
                        ->orWhereIn('class_id', $managedClassIds);
                });
            } elseif ($type === UserType::TYPE_DIRECTOR) {
                $allowedAppIds = Role::where('user_id', $user->id)
                    ->where('permission', 'like', 'p_%')
                    ->get()
                    ->map(fn($r) => (int) substr($r->permission, 2))
                    ->toArray();

                $q->whereIn('app_id', $allowedAppIds);
            }
        }

        $q->orderBy('start_datetime', 'DESC');

        return $q->paginate(20);
    }

    public function getListApi($params = [])
    {
        $query = $this->getModel()
            ->with(['practices.week.book']);

        if (!empty($params['app_id'])) {
            $query->where('app_id', $params['app_id']);
        }

        $query->where('status', '!=', \App\Enums\EventConstant::STATUS_DRAFT);

        if (!empty($params['per_page']) && !empty($params['page'])) {
            $perPage = (int) $params['per_page'];
            $page = (int) $params['page'];

            if ($perPage > 0 && $page > 0) {
                $offset = ($page - 1) * $perPage;

                $query->limit($perPage)
                    ->offset($offset);
            }
        }

        return $query
            ->orderBy('created_at', 'DESC')
            ->get();
    }

     public function getDetailApi($id)
    {
        $query = $this->getModel()->with(['practices.week.book']);

        $query->where('id', $id);

        return $query->orderBy('created_at', 'DESC')->first();
    }

    public function getEventNotCachedRank()
    {
        return $this->getModel()
            ->where('end_datetime', '<=', now())
            ->where('is_cached_rank', EventConstant::NOT_CACHED_RANK)
            ->get();
    }
}

<?php

namespace App\Repositories;

use App\Enums\AppConstant;
use App\Enums\UserAccessModuleConstant;
use App\Enums\UserTypeConstant;
use App\Models\App;
use App\Models\Role;
use App\Models\UserAccessModule;
use Illuminate\Support\Facades\DB;
use App\Models\UserType;

class AppRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return App::class;
    }

    public function searchByFilters($filters, $accessAppIds = [], $withoutPoint = false)
    {
        $user = auth()->user();
        $query = $this->model->query();

        if ($user->userType->type === UserType::TYPE_DIRECTOR) {
            $directorAppIds = Role::where('user_id', $user->id)
                ->where('permission', 'like', 'p_%')
                ->pluck('permission')
                ->map(fn($p) => (int) str_replace('p_', '', $p))
                ->toArray();
            $query = $query->whereIn('app.id', $directorAppIds);
        }

        if (!$withoutPoint) {
            $query = $query->withCount(['classes as classes_with_points' => function($q) {
                $q->join('points', 'classes.id', '=', 'points.class_id');
            }]);
        }
    
        if (!empty($filters['search'])) {
            $query->where('app.name', 'like', '%' . $filters['search'] . '%');
        }

        if ($user->user_type_id == UserTypeConstant::TYPE_EDITOR) {
            $userId = $user->id;
    
            $query->leftJoin('role', function ($q) use ($userId) {
                $q->where('role.user_id', $userId)
                    ->on('role.permission', '=', 'app.id');
            });
    
            $query->where(function ($q) use ($accessAppIds) {
                $q->where(function ($q2) use ($accessAppIds) {
                    $q2->whereIn('app.id', $accessAppIds)
                        ->whereNull('role.permission');
                })
                ->orWhereNotNull('role.permission');
            });
        }
      
        if($user->userType->type === UserType::TYPE_TEACHER || $user->userType->type === UserType::TYPE_TEACHER_ADMIN) {
            $query->whereIn('id', $accessAppIds);
        }
    
        $query = $query->selectRaw("
            app.id,
            app.name,
            app.status,
            app.img
        ")
        ->orderBy('app.sort_number', 'ASC');
    
        return $query->paginate(self::pagingItem);
    }

    public function getPrevById($recordTo)
    {
        return $this->model->where('sort_number', '<', $recordTo->sort_number)
            ->orderBy('sort_number', 'desc')
            ->first();
    }

    public function getNextById($recordTo)
    {
        return $this->model->where('sort_number', '>', $recordTo->sort_number)
            ->orderBy('sort_number', 'asc')
            ->first();
    }

    public function getListByUser($userId)
    {
        $query = $this->model->where('app.status', AppConstant::ON)
            ->leftJoin('role', function ($q) use ($userId) {
                $q->where('role.user_id', $userId)
                    ->where(function ($q2) {
                        $q2->on('role.permission', '=', DB::raw("CONCAT('p_', app.id)"))
                           ->orOn('role.permission', '=', DB::raw("CAST(app.id AS CHAR)"));
                    });
            })
            ->selectRaw("
                app.id,
                app.name,
                app.url,
                IF(role.id IS NULL, 0, 1) AS assigned
            ");

        return $query->paginate(self::pagingItem);
    }

    public function getListByRole($userId = null)
    {
        $roles = Role::where('user_id', $userId ?? auth()->id())->get();
        $appIds = [];
        foreach ($roles as $r) {
            if (!empty($r->permission) && str_starts_with($r->permission, 'p_')) {
                $appIds[] = str_replace('p_', '', $r->permission);
            }
        }
        return $this->select()
            ->whereIn('id', $appIds)
            ->get();
    }

    public function getAppWithAccessModule($userId)
    {
        $appTable = $this->model->getTable();
        $userAccessModuleTable = UserAccessModule::getTable();

        return $this->select()
            ->join($userAccessModuleTable, $userAccessModuleTable . '.module_id', '=', $appTable . '.id')
            ->where($userAccessModuleTable . '.user_id', $userId)
            ->where($userAccessModuleTable . '.module_type', UserAccessModuleConstant::MODULE_TYPE_APP)
            ->get();
    }
}

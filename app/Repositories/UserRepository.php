<?php

namespace App\Repositories;

use App\Enums\PointConstant;
use App\Models\App;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserRepository extends AbstractRepository
{
    const SEARCH_APP_NAME = 1;
    const SEARCH_CLASS_NAME = 2;
    const SEARCH_USER_NAME = 3;
    const SEARCH_NAME = 4;
    const SEARCH_TEL = 5;
    const LIST_TYPE_SEARCH = [
        UserRepository::SEARCH_USER_NAME => 'username',
        UserRepository::SEARCH_NAME => 'name',
        UserRepository::SEARCH_TEL => 'tel'
    ];

    public function getModelClass()
    {
        return User::class;
    }

    public function search($params = [])
    {
        $query = $this->model->query();

        if (!empty($params['is_app'])) {
            $query = $query->where('user_id_app', '!=', '');
        }

        if (!empty($params['user_ids'])) {
            $query = $query->whereIn('id', $params['user_ids']);
        }

        if (!empty($params['search'])) {
            $search = $params['search'];
            $query = $query->where(function ($query) use ($search) {
                $query->where('email', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%')
                    ->orWhere('tel', 'like', '%' . $search . '%');

                if (is_numeric($search)) {
                    $query->orWhere('id', $search);
                }
            });
        }

        if (!empty($params['user_id'])) {
            $userId = $params['user_id'];
            $query = $query->with(['userFollow' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }]);
            $query = $query->where('id', '!=', $userId);
        }

        if (!empty($params['limit'])) {
            $limit = $params['limit'];
            return $query->paginate($limit);
        }

        return $query->get();
    }

    public function getListByType($type)
    {
        $query = $this->model->where('user_type_id', $type)
            ->with(['userApp']);

        return $query->paginate(20);
    }

    public function getUserByEmail($id, $email)
    {
        return $this->model->where('email', $email)
            ->where('id', '<>', $id)
            ->first();
    }

    public function searchByFilters($filters)
    {
        $query = $this->model->query();

        if (!empty($filters['app_id'])) {
            $query->whereHas('userApp', function ($q) use ($filters) {
                $q->where('permission', $filters['app_id']);
            });
        }

        return $query->paginate(self::pagingItem);
    }

    public function getListDirector($filters)
    {
        $query = $this->model->query();
        $query = $query->with('directorApps');
        $query = $query->whereHas('userType', function ($q) {
            $q->where('type', UserType::TYPE_DIRECTOR);
        });

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate(self::pagingItem);
    }

    public function getListTeacher($filters)
    {

        $user = Auth::user();
        $query = $this->model->query();
        $query = $query->with(['teacherApp.app', 'practicesClasses', 'userApp']);
        $query = $query->whereHas('userType', function ($q) {
            $q->where('type', UserType::TYPE_TEACHER);
        });

        if ($user->userType->type === UserType::TYPE_DIRECTOR) {
            $directorAppPermissions = \App\Models\Role::where('user_id', $user->id)
                ->where('permission', 'like', 'p_%')
                ->pluck('permission')
                ->toArray();

            $query = $query->where(function ($q) use ($user, $directorAppPermissions) {
                $q->where('create_id', $user->id)
                  ->orWhereHas('userApp', function ($q2) use ($directorAppPermissions) {
                      $q2->whereIn('permission', $directorAppPermissions);
                  });
            });
        }

        if (!empty($filters['type_id'])) {
            $name = $filters['search'];
            if (in_array($filters['type_id'], [UserRepository::SEARCH_USER_NAME, UserRepository::SEARCH_NAME, UserRepository::SEARCH_TEL])) {
                $query->where(UserRepository::LIST_TYPE_SEARCH[$filters['type_id']], 'like', '%' . $filters['search'] . '%');
            } elseif ($filters['type_id'] == UserRepository::SEARCH_APP_NAME) {
                $query = $query->whereHas('teacherApp.app', function ($q) use ($name) {
                    $q->where('name', 'like', '%' . $name . '%');
                });
            } else {
                $query = $query->whereHas('teacherApp', function ($q) use ($name) {
                    $q->where('name', 'like', '%' . $name . '%');
                });
            }
        }

        $paginated = $query->paginate(self::pagingItem);

        // Batch-load app names từ role permission (p_{id}) để tránh N+1
        $appIds = $paginated->getCollection()->flatMap(function ($teacher) {
            return $teacher->userApp
                ->filter(fn($r) => str_starts_with($r->permission, 'p_'))
                ->map(fn($r) => (int) str_replace('p_', '', $r->permission));
        })->unique()->filter()->values();

        $apps = App::whereIn('id', $appIds)->pluck('name', 'id');

        $paginated->getCollection()->transform(function ($teacher) use ($apps) {
            // Ưu tiên app từ class, fallback sang role
            $appName = $teacher->teacherApp->first()?->app?->name;
            if (!$appName) {
                $pRole = $teacher->userApp->first(fn($r) => str_starts_with($r->permission, 'p_'));
                if ($pRole) {
                    $appId = (int) str_replace('p_', '', $pRole->permission);
                    $appName = $apps->get($appId, '');
                }
            }
            $teacher->setAttribute('app_name', $appName ?? '');
            return $teacher;
        });

        return $paginated;
    }

    public function getListStudent($filters)
    {
        $user = Auth::user();
        $query = $this->model->query();
        $query = $query->with(['classes.app', 'studentApp', 'orders']);
        $query = $query->whereHas('userType', function ($q) {
            $q->where('type', UserType::TYPE_STUDENT_APP);
        });
    
        if ($user->userType->type === UserType::TYPE_DIRECTOR) {
            $query = $query->whereHas('studentApp', function ($q) use($user) {
                $q->where('app_id', $user->directorApp->id);
            });
        }
        $name = data_get($filters, 'search');
        if (!empty($filters['type_id'])) {
            $name = $filters['search'];
            if (in_array($filters['type_id'], [UserRepository::SEARCH_USER_NAME, UserRepository::SEARCH_NAME, UserRepository::SEARCH_TEL])) {
                $query->where(UserRepository::LIST_TYPE_SEARCH[$filters['type_id']], 'like', '%' . $filters['search'] . '%');
            } elseif ($filters['type_id'] == UserRepository::SEARCH_APP_NAME) {
                $query = $query->whereHas('studentApp', function ($q) use ($name) {
                    $q->where('name', 'like', '%'. $name . '%');
                });
            } else {
                $query = $query->whereHas('classes', function ($query) use ($name) {
                    $query->where('name', 'like', '%'. $name . '%');
                });
            }
        }else if(!empty($name)){
            $query->where(function($q) use($name){
                $q->where('name', 'like', '%'. $name . '%')
                    ->orWhere('email', 'like', '%'. $name . '%')
                    ->orWhere('username', 'like', '%'. $name . '%')
                    ->orWhere('tel', 'like', '%'. $name . '%');
            });
        }

        if (!empty($filters['class_id'])) {
            $classId = $filters['class_id'];
            $query = $query->whereHas('classes', function ($q) use ($classId) {
                $q->where('classes.id', $classId);
            });
        }

        if (isset($filters['class_ids']) && !empty($filters['class_ids'])) {
            $classIds = $filters['class_ids'];
            $query = $query->whereHas('classes', function ($q) use ($classIds) {
                $q->whereIn('classes.id', $classIds);
            });
        }

        return $query->paginate(self::pagingItem);
    }

    public function getListStudentResultPractice($studentIds, $practiceId)
    {
        $query = $this->model->query();
        $query = $query->whereIn('id', $studentIds)->with([
            'bestPoint' => function ($q1) use ($practiceId) {
            $q1->where('practice_id', $practiceId)->with(['practiceInfos']);
        },
            'studentClass.practiceClass' => function ($q2) use ($practiceId) {
            $q2->where('practice_id', $practiceId);
        }
        ]);

        return $query->paginate(self::pagingItem);
    }

    public function getListStudentResultClass($studentIds, $classId, $type)
    {
        $query = $this->model->query();
        $pointIds = $this->getFilteredPointIds($classId, $type);

        return $query->whereIn('id', $studentIds)
            ->with([
                'bestPointsPerPractice' => function ($q) use ($pointIds, $type) {
                    $q->whereIn('points.id', $pointIds)
                        ->with('practiceInfos');

                    if ((int)$type !== -1) {
                        $q->where('type', $type);
                    }
                },
            ])
            ->withSum(
                ['bestPointsPerPractice as total_star' => function ($q) use ($pointIds) {
                    $q->whereIn('points.id', $pointIds);
                }],
                'star_count'
            )
            ->orderByDesc('total_star') // sắp xếp toàn danh sách theo điểm
            ->paginate(self::pagingItem);
    }

    public function getListStudentResultSchool($studentIds, $type)
    {
        $query = $this->model->query();
        $pointIds = $this->getFilteredPointIdsSchool($type);

        $query = $query->whereIn('id', $studentIds)->with([
            'bestPointsPerPractice' => function ($q1) use ($pointIds, $type) {
                $q1->whereIn('points.id', $pointIds)
                    ->with('practiceInfos');

                if ((int)$type !== -1) {
                    $q1->where('type', $type);
                }
            },
        ]);

        return $query->paginate(self::pagingItem);
    }

    protected function getFilteredPointIdsSchool($type)
    {
        $baseQuery = DB::table('points')
            ->select(
                'id',
                'user_id',
                'practice_id',
                'class_id',
                'type',
                DB::raw('ROW_NUMBER() OVER (
                PARTITION BY user_id, practice_id, class_id' .
                    ((int)$type === -1 ? ', type' : '') . '
                ORDER BY star_count DESC, time ASC
            ) as rn')
            )
            ->where(function ($q) {
                $q->where('is_assign', 2)->orWhereNull('is_assign');
            });

        if ((int)$type !== -1) {
            $baseQuery->where('type', $type);
        }

        $subQuery = DB::table(DB::raw("({$baseQuery->toSql()}) as ranked"))
            ->mergeBindings($baseQuery)
            ->where('ranked.rn', 1);

        return $subQuery->pluck('ranked.id')->toArray();
    }
    protected function getFilteredPointIds($classId, $type)
    {
        $baseQuery = DB::table('points')
            ->select(
                'id',
                'user_id',
                'practice_id',
                'class_id',
                'type',
                DB::raw('ROW_NUMBER() OVER (
                PARTITION BY user_id, practice_id, class_id' .
                    ((int)$type === -1 ? ', type' : '') . '
                ORDER BY star_count DESC, time ASC
            ) as rn')
            )
            ->where('class_id', $classId)
            ->where(function ($q) {
                $q->where('is_assign', 2)->orWhereNull('is_assign');
            });

        if ((int)$type !== -1) {
            $baseQuery->where('type', $type);
        }

        $subQuery = DB::table(DB::raw("({$baseQuery->toSql()}) as ranked"))
            ->mergeBindings($baseQuery)
            ->where('ranked.rn', 1);

        return $subQuery->pluck('ranked.id')->toArray();
    }
}

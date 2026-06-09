<?php

namespace App\Services;

use App\Enums\UserAccessModuleConstant;
use App\Models\Role;
use App\Models\UserType;
use App\Repositories\BookRepository;
use App\Repositories\UserAccessModuleRepository;
use App\Repositories\WeekRepository;
use Illuminate\Support\Facades\DB;

class UserAccessModuleService
{
    public function __construct(
        private UserAccessModuleRepository $userAccessModuleRepository,
        private BookRepository $bookRepository,
        private WeekRepository $weekRepository
    ) {}

    public function updateAccessModule($params)
    {
        $this->userAccessModuleRepository->deleteByFilter([
            'user_id' => $params['user_id'],
        ]);

        foreach ($params['app_ids'] as $appId) {
            $userAccessModule[] = [
                'user_id' => $params['user_id'],
                'module_id' => $appId,
                'module_type' => UserAccessModuleConstant::MODULE_TYPE_APP,
                'permission' => UserAccessModuleConstant::PERMISSION_FULL,
            ];
        }

        foreach ($params['book_ids'] as $bookId) {
            $userAccessModule[] = [
                'user_id' => $params['user_id'],
                'module_id' => $bookId,
                'module_type' => UserAccessModuleConstant::MODULE_TYPE_BOOK,
                'permission' => UserAccessModuleConstant::PERMISSION_FULL,
            ];
        }

        foreach ($params['week_ids'] as $weekId) {
            $userAccessModule[] = [
                'user_id' => $params['user_id'],
                'module_id' => $weekId,
                'module_type' => UserAccessModuleConstant::MODULE_TYPE_WEEK,
                'permission' => UserAccessModuleConstant::PERMISSION_FULL,
            ];
        }

        return $this->userAccessModuleRepository->insert($userAccessModule);
    }

    public function getListByUserId($userId)
    {
        return $this->userAccessModuleRepository->getListByUserId($userId);
    }

    public function getAppIds()
    {
        if (auth()->user()->isAdmin()) {
            return [[],[]];
        }
        $userAccessModules = $this->userAccessModuleRepository->getListByUserId(auth()->user()->id);
      
        $appIds = [];
        $parentAppIds = [];
        $user = auth()->user();
  
        if($user->user_type_id == 4 || $user->user_type_id == 111) {
            $listApp = DB::table('role')
                    ->where('user_id', $user->id)
                    ->get();
            foreach($listApp as $app) {
                $perm = $app->permission;
                if (str_starts_with($perm, 'p_')) {
                    $appIds[] = (int)str_replace('p_', '', $perm);
                } elseif (str_starts_with($perm, 'b_')) {
                    $appIds[] = (int)str_replace('b_', '', $perm);
                } elseif (is_numeric($perm)) {
                    $appIds[] = (int)$perm;
                }
            }
        }
    
        foreach ($userAccessModules as $userAccessModule) {
            
            if ($userAccessModule->module_type == UserAccessModuleConstant::MODULE_TYPE_APP) {
                $appIds[] = $userAccessModule->module_id;
            }

            if ($userAccessModule->module_type == UserAccessModuleConstant::MODULE_TYPE_BOOK) {
                if (data_get($userAccessModule, 'book.app_id')) {
                    $parentAppIds[] = data_get($userAccessModule, 'book.app_id');
                }
            }

            if ($userAccessModule->module_type == UserAccessModuleConstant::MODULE_TYPE_WEEK) {
                if (data_get($userAccessModule, 'week.book.app_id')) {
                    $parentAppIds[] = data_get($userAccessModule, 'week.book.app_id');
                }
            }
        }
  
        return [$appIds, $parentAppIds];
    }

    public function getBookIds($appId = null)
    {
        $user = auth()->user();
        $userType = $user->userType->type;
        $bookIds = [];
        $parentBookIds = [];

        // Admin xem tất cả
        if ($userType === UserType::TYPE_ADMIN) {
            return [[], []];
        }

        // Director: xem tất cả book trong app được gán qua role p_
        if ($userType === UserType::TYPE_DIRECTOR) {
            $roleAppIds = Role::where('user_id', $user->id)
                ->where('permission', 'like', 'p_%')
                ->pluck('permission')
                ->map(fn($p) => (int) str_replace('p_', '', $p))
                ->filter()->unique()->values()->toArray();

            if (!empty($roleAppIds)) {
                $q = DB::table('book')->whereIn('app_id', $roleAppIds);
                if ($appId) {
                    $q->where('app_id', $appId);
                }
                $bookIds = $q->pluck('id')->toArray();
            }

            return [$bookIds, $parentBookIds];
        }

        // Teacher: chỉ thấy book được gán trực tiếp (book.user_id = user->id)
        // b_{app_id} chỉ dùng để kiểm tra quyền truy cập trang, không dùng để lọc danh sách book
        if ($userType === UserType::TYPE_TEACHER) {
            $q = DB::table('book')->where('user_id', $user->id);

            if ($appId) {
                $q->where('app_id', $appId);
            }

            return [$q->pluck('id')->toArray(), $parentBookIds];
        }
      
        // Editor và các role khác: dùng UserAccessModule
        $ownerQuery = $this->bookRepository->where('user_id', $user->id);
        if ($appId) {
            $ownerQuery = $ownerQuery->where('app_id', $appId);
        }
        $ownerBookIds = $ownerQuery->get()->pluck('id')->toArray();
        $userAccessModules = $this->userAccessModuleRepository->getListByUserId($user->id);

        foreach ($userAccessModules as $userAccessModule) {
            if ($userAccessModule->module_type == UserAccessModuleConstant::MODULE_TYPE_APP) {
                if (!$appId || $appId == $userAccessModule->module_id) {
                    foreach (data_get($userAccessModule, 'app.books', []) as $book) {
                        $bookIds[] = $book->id;
                    }
                }
            }

            if ($userAccessModule->module_type == UserAccessModuleConstant::MODULE_TYPE_BOOK) {
                $bookAppId = data_get($userAccessModule, 'book.app_id');
                if (!$appId || $bookAppId == $appId) {
                    $bookIds[] = $userAccessModule->module_id;
                }
            }

            if ($userAccessModule->module_type == UserAccessModuleConstant::MODULE_TYPE_WEEK) {
                $weekBookAppId = data_get($userAccessModule, 'week.book.app_id');
                if (!$appId || $weekBookAppId == $appId) {
                    if (data_get($userAccessModule, 'week.book_id')) {
                        $parentBookIds[] = data_get($userAccessModule, 'week.book_id');
                    }
                }
            }
        }

        return [array_merge($bookIds, $ownerBookIds), $parentBookIds];
    }

    public function getWeekIds($bookId = null)
    {
        if (auth()->user()->isAdmin()) {
            return [];
        }

        $user = auth()->user();

        // Teacher: thấy tất cả week thuộc các book GV được phép quản lý
        if ($user->userType->type === UserType::TYPE_TEACHER) {
            [$allowedBookIds] = $this->getBookIds();

            if (empty($allowedBookIds)) {
                return [];
            }

            $q = DB::table('week')->whereIn('book_id', $allowedBookIds);
            if ($bookId) {
                $q->where('book_id', $bookId);
            }
            return $q->pluck('id')->toArray();
        }

        $ownerWeekIds = $this->weekRepository->where('user_id', $user->id)->get()->pluck('id');
        $userAccessModules = $this->userAccessModuleRepository->getListByUserId($user->id);

        $weekIds = [];

        foreach ($userAccessModules as $userAccessModule) {
            if ($bookId && $userAccessModule->module_type == UserAccessModuleConstant::MODULE_TYPE_BOOK) {
                if ($bookId == $userAccessModule->module_id) {
                    foreach (data_get($userAccessModule, 'book.weeks', []) as $week) {
                        $weekIds[] = $week->id;
                    }
                }
            }

            if ($userAccessModule->module_type == UserAccessModuleConstant::MODULE_TYPE_WEEK) {
                $weekIds[] = $userAccessModule->module_id;
            }
        }

        return array_merge($weekIds, $ownerWeekIds->toArray());
    }
}

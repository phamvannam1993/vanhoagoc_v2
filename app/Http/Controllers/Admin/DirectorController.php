<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\User;
use App\Models\UserType;
use App\Services\AppService;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class DirectorController extends Controller
{
    protected $userService;
    protected $appService;
    protected $roleService;

    public function __construct(UserService $userService, AppService $appService, RoleService $roleService)
    {
        $this->userService = $userService;
        $this->appService = $appService;
        $this->roleService = $roleService;
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Director/Index', [
            'query' => $request->query()
        ]);
    }

    public function create(Request $request): Response
    {
        $listApp = $this->appService->getListFull();

        return Inertia::render('Admin/Director/Create', [
            'query' => $request->query(),
            'listApp' => $listApp
        ]);
    }

    public function edit(Request $request): Response
    {
        $user = $this->userService->detailDirector($request);
        $listApp = $this->appService->getListFull();

        return Inertia::render('Admin/Director/Edit', [
            'query' => $request->query(),
            'listApp' => $listApp,
            'user' => $user
        ]);
    }

    public function jsonList(Request $request)
    {
        $params = $request->all();
        $list = $this->userService->getListDirector($params);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $data['user_type_id'] = UserType::where('type', UserType::TYPE_DIRECTOR)->first()->id;
            DB::beginTransaction();
            $user = $this->userService->store($data);

            if ($user) {
                $appIds = is_array($data['app_ids'] ?? null) ? $data['app_ids'] : (isset($data['app_id']) ? [$data['app_id']] : []);

                // Xóa app_ids cũ không còn trong danh sách mới
                App::where('user_id', $user->id)->whereNotIn('id', $appIds)->update(['user_id' => null]);

                // Gán user_id cho các app mới
                App::whereIn('id', $appIds)->update(['user_id' => $user->id]);

                // Xóa role cũ và gán role mới cho từng app
                $this->roleService->deleteRoleDirector($user->id);
                foreach ($appIds as $appId) {
                    $this->roleService->assignRoleUser([
                        'user_id' => $user->id,
                        'app_id'  => 'p_' . $appId,
                        'type_id' => $data['user_type_id'],
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'status' => true,
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            Log::info($exception->getLine());

            return response()->json([
                'status' => false,
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $user = User::where('id', $id)->with('directorApps')->firstOrFail();
            DB::beginTransaction();
            if ($user) {
                App::where('user_id', $user->id)->update(['user_id' => null]);
                $this->roleService->deleteRoleDirector($user->id);
                $this->userService->delete($id);
            }
            DB::commit();
            return response()->json([
                'status' => true
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());

            return response()->json([
                'status' => false,
            ]);
        }
    }

    public function deleteMultiple(Request $request)
    {
        try {
            $ids = $request->input('ids');
            $users = User::whereIn('id', $ids)->get();
            DB::beginTransaction();

            if (!empty($users)) {
                foreach ($users as $key => $user) {
                    App::where('user_id', $user->id)->update(['user_id' => null]);
                    $this->roleService->deleteRoleDirector($user->id);
                    $this->userService->delete($user->id);
                }
            }
            DB::commit();
            return response()->json([
                'status' => true
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());

            return response()->json([
                'status' => false,
            ]);
        }
    }

    public function resetPassword($id)
    {
        $user = $this->userService->findById($id);

        if (!$user) {
            return response()->json([
                'status' => false,
            ]);
        }

        $this->userService->resetPassword($id);

        return response()->json([
            'status' => true
        ]);
    }
}

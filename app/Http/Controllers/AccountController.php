<?php

namespace App\Http\Controllers;

use App\Enums\UserTypeConstant;
use App\Helpers\Helper;
use App\Services\AppService;
use App\Services\UserAccessModuleService;
use App\Services\UserService;
use App\Services\UserTypeService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AccountController extends BaseModuleController
{
    protected $userService;
    protected $userTypeService;
    protected $appService;
    protected $userAccessModuleService;
    public function __construct(
        UserService $userService,
        UserTypeService $userTypeService,
        AppService $appService,
        UserAccessModuleService $userAccessModuleService
    ) {
        $this->userService = $userService;
        $this->userTypeService = $userTypeService;
        $this->appService = $appService;
        $this->userAccessModuleService = $userAccessModuleService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Account/Index', [
            'query' => $request->query(),
        ]);
    }

    public function newMember(Request $request, UserTypeService $userTypeService)
    {
        $types = $userTypeService->getAvailableType();

        return Inertia::render('Account/CreateMember', [
            'query' => $request->query(),
            'types' => $types
        ]);
    }

    public function member(Request $request)
    {
        return Inertia::render('Account/Member', [
            'query' => $request->query(),
        ]);
    }

    public function updateAccount(Request $request, UserService $userService)
    {
        $user = auth()->user();
        if ($user->user_type_id == UserTypeConstant::TYPE_EDITOR) {
            $data['user_id'] = $user->id;
        } else {
            $data = $request->all(['user_id']);
        }

        $record = $userService->detail($data);
        $types = $this->userTypeService->getAvailableType();

        return Inertia::render('Account/UpdateAccount', [
            'user' => $record,
            'query' => $request->query(),
            'types' => $types
        ]);
    }

    public function assignRole(Request $request)
    {
        return Inertia::render('Account/AssignRole', [
            'query' => $request->query(),
        ]);
    }

    public function editMember(Request $request, UserService $userService)
    {
        $data = $request->all(['user_id']);
        $types = $this->userTypeService->getAvailableType();
        $record = $userService->detail($data);

        return Inertia::render('Account/EditMember', [
            'user' => $record,
            'query' => $request->query(),
            'types' => $types
        ]);
    }

    public function jsonList(UserTypeService $userTypeService)
    {
        $list = $userTypeService->getList();

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function jsonListMember(Request $request, UserTypeService $userTypeService)
    {
        $params = $request->all([
            'type_id'
        ]);

        $list = $userTypeService->getListByType($params);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function jsonListApp(Request $request, UserTypeService $userTypeService)
    {
        $params = $request->all('user_id');

        $list = $userTypeService->getListAppByUser($params['user_id']);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function editAccessModule(Request $request)
    {
        return Inertia::render('Account/EditAccessModule', [
            'query' => $request->query(),
        ]);
    }

    public function jsonEditAccessModule(Request $request)
    {
        $params = $request->all([
            'user_id'
        ]);

        $list = $this->userTypeService->getListAppByUser($params['user_id']);
        $list->load(['books.weeks']);

        $userModules = $this->userAccessModuleService->getListByUserId($params['user_id']);

        return response()->json([
            'status' => true,
            'data' => $list,
            'userModules' => $userModules
        ]);
    }

    public function updateAccessModule(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required',
                'app_ids' => 'nullable',
                'book_ids' => 'nullable',
                'week_ids' => 'nullable'
            ]);

            $this->userAccessModuleService->updateAccessModule($request->all());

            return response()->json([
                'status' => true,
                'message' =>     'Cập nhật quyền sử dụng thành công'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->errors()
            ]);
        } catch (\Exception $e) {
            Helper::logException($e);
            return response()->json([
                'status' => false,
                'message' => 'Cập nhật quyền sử dụng thất bại'
            ]);
        }
    }

    public function jsonCreateMember(Request $request, UserTypeService $userTypeService)
    {
        $params = $request->all([
            'type_id',
            'name',
            'password',
            'phone_number',
            'email'
        ]);

        $result = $userTypeService->createMember($params);

        if (empty($result['status'])) {
            return response()->json([
                'status' => false,
                'messages' => $result['messages']
            ]);
        } else {
            return response()->json([
                'status' => true,
                'data' => [
                    'redirectUrl' => route('users.member', ['type_id' => $params['type_id']])
                ]
            ]);
        }
    }

    public function jsonEditMember(Request $request, UserTypeService $userTypeService)
    {
        $params = $request->all([
            'id',
            'type_id',
            'name',
            'password',
            'phone_number',
            'email',
            'img'
        ]);

        $result = $userTypeService->updateMember($params);

        if (empty($result['status'])) {
            return response()->json([
                'status' => false,
                'messages' => $result['messages']
            ]);
        } else {
            return response()->json([
                'status' => true,
                'data' => [
                    'redirectUrl' => route('users.member', ['type_id' => $params['type_id']])
                ]
            ]);
        }
    }

    public function jsonUpdateMember(Request $request, UserTypeService $userTypeService)
    {
        $params = $request->all([
            'id',
            'type_id',
            'name',
            'password',
            'phone_number',
            'email',
            'img'
        ]);

        $result = $userTypeService->updateMember($params);

        if (empty($result['status'])) {
            return response()->json([
                'status' => false,
                'messages' => $result['messages']
            ]);
        } else {
            return response()->json([
                'status' => true,
                'data' => [
                    'redirectUrl' => route('users.member', ['type_id' => $params['type_id']])
                ]
            ]);
        }
    }

    public function jsonAssignRole(Request $request, UserTypeService $userTypeService)
    {
        $params = $request->all([
            'user_id',
            'app_id',
            'checked',
            'type_id'
        ]);

        $userTypeService->assignRole($params);

        return response()->json([
            'status' => true
        ]);
    }

    public function deleteMember($id, UserTypeService $userTypeService)
    {
        $result = $userTypeService->delete($id);

        if (empty($result['status'])) {
            return response()->json([
                'status' => false,
                'messages' => $result['messages']
            ]);
        } else {
            return response()->json([
                'status' => true,
                'data' => [
                    'redirectUrl' => route('users.member', ['type_id' => $result['type_id']])
                ]
            ]);
        }
    }

    public function list(Request $request)
    {
        $listApp = $this->appService->getListFull();

        return Inertia::render('Account/User', [
            'query' => $request->query(),
            'listApp' => $listApp
        ]);
    }

    public function jsonListUser (Request $request)
    {
        $params = $request->all([
            'app_id',
        ]);
        $list = $this->userService->getListUser($params);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use App\Repositories\UserRoomRepository;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends BaseModuleController
{
    public function __construct(
        private UserService $userService
    ) {
    }

    public function register(Request $request, UserService $userService)
    {
        try {
            $request->validate([
                'username' => 'required|unique:users,username',
                'email'    => 'required|unique:users,email',
                'phone'    => 'required',
                'user_id'     => 'required|unique:users,user_id_app',
            ],
            [
                'username.required' => 'Vui lòng nhập tên người dùng.',
                'email.required'    => 'Vui lòng nhập địa chỉ email.',
                'phone.required'    => 'Vui lòng nhập số điện thoại.',
                'user_id.required'    => 'Vui lòng nhập user id.',

                'username.unique'   => 'Tên người dùng đã tồn tại.',
                'email.unique'      => 'Email đã tồn tại.',
                'phone.unique'    => 'Số điện thoại đã tồn tại.',
                'user_id.unique'    => 'User id đã tồn tại.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success'=> false,
                'message' => $e->getMessage()
            ], 422);
        }

        $data = $request->all();

        $result = $userService->register($data);

        return response()->json($result, empty($result['success']) ? 400 : 200);
    }

    public function update(Request $request, UserService $userService)
    {
        $id = $request->user_id;

        try {
            $request->validate([
                'user_id'     => 'required',
                'username' => 'nullable|unique:users,username,'.$id,
                'email'    => 'nullable|unique:users,email,'.$id,
                'phone'    => 'nullable|unique:users,tel,'.$id,
            ],
            [
                'user_id.required'    => 'Vui lòng nhập user id.',
                'username.unique'   => 'Tên người dùng đã tồn tại.',
                'email.unique'      => 'Email đã tồn tại.',
                'phone.unique'    => 'Số điện thoại đã tồn tại.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success'=> false,
                'message' => $e->getMessage()
            ], 422);
        }

        $data = $request->all();

        $result = $userService->update($data);

        return response()->json($result, empty($result['success']) ? 400 : 200);
    }

    public function getDetail(Request $request, UserService $userService)
    {
        $data = $request->all();

        if (empty($data['user_profile_id'])) {
            $data['user_profile_id'] = $data['user_id'];
        }
        $user = $userService->detail($data);

        return response()->json([
            'success' => true,
            'data' =>  $user
        ], 200);
    }

    public function uploadAvatar(Request $request, UserService $userService)
    {
        $userId = $request['user_id'];
        $img = $request->file('avatar');

        $result = $userService->uploadAvatar($userId, $img);

        return response()->json($result, empty($result['success']) ? 400 : 200);
    }

    public function toolCreate(Request $request)
    {
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function toolUpdate(Request $request)
    {

    }

    public function getRoom(Request $request)
    {
        $data = $request->all();
        $result = app(UserRoomRepository::class)->getList($data);
        return $this->successResponse($result);
    }

    public function postRoom(Request $request)
    {
        $data = $request->all();
        $result = app(UserRoomRepository::class)->create($data);
        return $this->successResponse($result);
    }

    public function getAchievements(Request $request)
    {
        try {
            $result = $this->userService->getAchievements($request->all());
            return $this->successResponse($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi không xác định'
            ], 500);
        }
    }
}

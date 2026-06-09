<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use App\Services\UserFollowService;
use Illuminate\Http\Request;

class UserFollowController extends BaseModuleController
{
    public function searchUser(Request $request, UserFollowService $userFollowService)
    {
        $params = $request->all();
        $params['is_app'] = true;
        if (!isset($params['limit'])) {
            $params['limit'] = 100;
        }

        $users = $userFollowService->getlistUsers($params);

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    public function postUserFollow(Request $request, UserFollowService $userFollowService)
    {
        $params = $request->all();

        $userFollowService->save($params);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thành công'
        ]);
    }

    public function getUserFollow(Request $request, UserFollowService $userFollowService)
    {
        $params = $request->all();
        $data = $userFollowService->getList($params);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}

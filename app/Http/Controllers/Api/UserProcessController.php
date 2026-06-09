<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use App\Services\UserProcessService;
use Illuminate\Http\Request;

class UserProcessController extends BaseModuleController
{
    public function postUserProcess(Request $request, UserProcessService $userProcessService)
    {
        $data = $request->all();
        $userProcessService->save($data);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thành công'
        ], 200);
    }

    public function getUserProcess(Request $request, UserProcessService $userProcessService)
    {
        $params = $request->all();
        $data = $userProcessService->getList($params);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}

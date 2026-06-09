<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use App\Services\UserTaskService;
use Illuminate\Http\Request;

class UserTaskController extends BaseModuleController
{
    public function getUserTask(Request $request, UserTaskService $userTaskService)
    {
        $params = $request->all();
        $params['day'] = date('Y-m-d');

        $result = $userTaskService->getTask($params);

        return response()->json([
            'success' => true,
            'user_tasks' => $result
        ]);
    }

    public function postUserTask(Request $request, UserTaskService $userTaskService)
    {
        $params = $request->all();
        $result = $userTaskService->save($params);

        return response()->json($result, empty($result['success']) ? 400 : 200);
    }

    public function getTotalCompleted(Request $request, UserTaskService $userTaskService)
    {
        $params = $request->all();
        $total = $userTaskService->getTotalCompleted($params);

        return response()->json([
            'success' => true,
            'total' => $total
        ]);
    }
}

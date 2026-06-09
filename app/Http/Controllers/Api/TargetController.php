<?php

namespace App\Http\Controllers\Api;

use App\Enums\TargetConstant;
use App\Http\Controllers\BaseModuleController;
use App\Services\TargetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TargetController extends BaseModuleController
{
    public function daily(TargetService $targetService)
    {
        $list = $targetService->getDailyTarget();

        return response()->json([
            'targets' => $list
        ]);
    }

    public function sight(TargetService $targetService)
    {
        $list = $targetService->getSightTarget();

        return response()->json([
            'targets' => $list
        ]);
    }

    public function postDaily(Request $request, TargetService $targetService)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'user_id' => 'required',
            'target_item_id' => 'required|exists:target_items,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ]);
        } else {
            $params['target_type'] = TargetConstant::TYPE_DAILY;
            $result = $targetService->updateOrCreate($params);

            return response()->json($result);
        }
    }

    public function postSight(Request $request, TargetService $targetService)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'user_id' => 'required',
            'target_item_id' => 'required|exists:target_items,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ]);
        } else {
            $params['target_type'] = TargetConstant::TYPE_SIGHT;
            $result = $targetService->updateOrCreate($params);

            return response()->json($result);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Enums\PointConstant;
use App\Enums\PointDayConstant;
use App\Http\Controllers\BaseModuleController;
use App\Services\PointService;
use Illuminate\Http\Request;

class PointController extends BaseModuleController
{
    public function postPractice(Request $request, PointService $pointService)
    {
        $params = $request->all();

        $pointService->save($params, PointConstant::POINT_PRACTICE);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thành công'
        ]);
    }

    public function getPractice(Request $request, PointService $pointService)
    {
        $data = $request->all();
        $data['type'] = PointConstant::POINT_PRACTICE;
        $list = $pointService->getListByType($data);

        return response()->json([
            'success' => true,
            'practices' => $list
        ]);
    }

    public function postTheoretical(Request $request, PointService $pointService)
    {
        $params = $request->all();

        $pointService->save($params, PointConstant::POINT_THEORETICAL);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thành công'
        ]);
    }

    public function getTheoretical(Request $request, PointService $pointService)
    {
        $data = $request->all();
        $data['type'] = PointConstant::POINT_THEORETICAL;
        $list = $pointService->getListByType($data);

        return response()->json([
            'success' => true,
            'theoreticals' => $list
        ]);
    }

    public function getTotalPracticePoint(Request $request, PointService $pointService)
    {
        $data = $request->all();

        $result = $pointService->getSummaryPoint($data);


        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    public function getTotalTheoreticalPoint(Request $request, PointService $pointService)
    {
        $data = $request->all();
        $data['type'] = PointConstant::POINT_THEORETICAL;

        $result = $pointService->getSummaryPoint($data);

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    public function getListRank(Request $request, PointService $pointService)
    {
        $data = $request->all();
        if (empty($data['type'])) {
            $data['type'] = PointConstant::POINT_SUM;
        }

        $data = $pointService->getRank($data);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function getTotal(Request $request, PointService $pointService)
    {
        $params = $request->all();

        $total = $pointService->getTotalPoint($params);

        return response()->json([
            'success' => true,
            'total' => $total
        ], 200);
    }

    public function subTotal(Request $request, PointService $pointService)
    {
        $userId = $request->get('user_id');
        $type = PointDayConstant::POINT_HEART;

        $pointService->subTotalPoint($userId, $type);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thành công'
        ]);
    }

    public function getPracticePointDay(Request $request, PointService $pointService)
    {
        $params = $request->all();

        $data = $pointService->getPracticePointDay($params);

        return response()->json(["success" => true, "data" => $data], 200);
    }
}

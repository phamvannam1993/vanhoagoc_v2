<?php

namespace App\Http\Controllers\Hr;

use App\Enums\PointConstant;
use App\Http\Controllers\BaseModuleController;
use App\Services\Hr\PointService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PointController extends BaseModuleController
{
    public function index()
    {
        $types = [
            [
                'id' => PointConstant::POINT_PRACTICE,
                'name' => 'luyện tập'
            ],
            [
                'id' => PointConstant::POINT_THEORETICAL,
                'name' => 'lý thuyết'
            ]
        ];

        $params = [
            'typeList' => $types
        ];

        return Inertia::render('Hr/Point/Index', $params);
    }

    public function jsonList(Request $request, PointService $pointService)
    {
        $data = $request->all(['type']);

        $list = $pointService->getListByType($data);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }
}

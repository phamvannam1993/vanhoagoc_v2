<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use App\Services\AppService;
use App\Services\BookService;
use App\Services\StarService;
use Illuminate\Http\Request;

class StarController extends BaseModuleController
{
    public function postStar(Request $request, StarService $starService)
    {
        $data = $request->all();
        $starService->save($data);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thành công'
        ]);
    }

    public function getStar(Request $request, StarService $starService)
    {
        $data = $request->all();
        $star = $starService->getStar($data);

        return response()->json(['success' => true, 'data' => $star]);
    }
}

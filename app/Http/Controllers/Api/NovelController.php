<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use App\Services\NovelService;
use Illuminate\Http\Request;

class NovelController extends BaseModuleController
{
    public function getDeepview(Request $request, NovelService $novelService)
    {
        $params = $request->all();
        $data = $novelService->getDeepview($params);

        return response()->json($data, isset($data['success']) ? 400 : 200);
    }
}

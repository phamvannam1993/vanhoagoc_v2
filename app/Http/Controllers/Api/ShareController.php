<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use App\Services\ShareService;
use Illuminate\Http\Request;

class ShareController extends BaseModuleController
{
    public function postShare(Request $request, ShareService $shareService)
    {
        $dataRes = $shareService->save($request->all());

        return response()->json($dataRes, empty($dataRes['success']) ? 400 : 200);
    }

    public function getShare(Request $request, ShareService $shareService)
    {
        $dataRes = $shareService->getShare($request->all());

        return response()->json($dataRes, empty($dataRes['success']) ? 400 : 200);
    }

    public function getUserShare(Request $request, ShareService $shareService)
    {
        $dataRes = $shareService->getUserShare($request->all());

        return response()->json($dataRes, empty($dataRes['success']) ? 400 : 200);
    }

    public function postClickCount(Request $request, ShareService $shareService)
    {
        $dataRes = $shareService->postClickCount($request->all());

        return response()->json($dataRes, empty($dataRes['success']) ? 400 : 200);
    }
}

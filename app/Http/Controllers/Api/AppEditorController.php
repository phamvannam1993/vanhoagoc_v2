<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use App\Services\AppService;
use Illuminate\Http\Request;

class AppEditorController extends BaseModuleController
{
    public function getAppEditorByBook(Request $request, AppService $appService)
    {
        $params = $request->all();

        $data = $appService->getAppEditorByBook($params);

        return response()->json($data);
    }
}

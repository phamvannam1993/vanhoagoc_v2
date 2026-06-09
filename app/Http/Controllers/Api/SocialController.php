<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use App\Services\SocialService;
use Illuminate\Http\Request;

class SocialController extends BaseModuleController
{
    public function login(Request $request, SocialService $socialService)
    {
        $data = $request->all();

        $result = $socialService->login($data);

        return response()->json($result, empty($result['success']) ? 404 : 200);
    }
}

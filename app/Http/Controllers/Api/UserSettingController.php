<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use App\Services\UserSettingService;
use Illuminate\Http\Request;

class UserSettingController extends BaseModuleController
{
    public function postSetting(Request $request, UserSettingService $userSettingService)
    {
        $data = $request->all();
        $userSettingService->createOrUpdate($data);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thành công'
        ], 200);
    }

    public function getSetting(Request $request, UserSettingService $userSettingService)
    {
        $data = $request->all();

        $setting = $userSettingService->getDetail($data);

        return response()->json([
            'success' => true,
            'setting' => $setting
        ]);
    }
}

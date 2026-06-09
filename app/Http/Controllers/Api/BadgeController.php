<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use App\Services\BadgeService;
use Illuminate\Http\Request;

class BadgeController extends BaseModuleController
{
    public function getBadge(BadgeService $badgeService)
    {
        $badge = $badgeService->getBadge();
        return response()->json([
            'success' => true,
            'data' => $badge
        ]);
    }

    public function getBadgeDetail(Request $request, BadgeService $badgeService)
    {
        $id = $request->id;

        $badge = $badgeService->getDetail($id);

        return response()->json([
            'success' => true,
            'badge' => $badge
        ]);
    }

    public function postBadge(Request $request, BadgeService $badgeService)
    {
        $result = $badgeService->save($request->all());

        return response()->json($result, empty($result['success']) ? 400 : 200);
    }

    public function getUserBadge(Request $request, BadgeService $badgeService)
    {
        $userBadges = $badgeService->getUserBadge($request->all());

        return response()->json([
            'success' => true,
            'data' => $userBadges
        ]);
    }
}

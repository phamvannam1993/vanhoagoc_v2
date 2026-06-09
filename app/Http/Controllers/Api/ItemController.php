<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use App\Services\ItemService;
use Illuminate\Http\Request;

class ItemController extends BaseModuleController
{
    public function getAllItem(ItemService $itemService)
    {
        $items = $itemService->getAll();

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    public function getDetail(Request $request, ItemService $itemService)
    {
        $item = $itemService->getDetail($request->id);

        return response()->json([
            'success' => true,
            'item' => $item
        ]);
    }

    public function purchase(Request $request, ItemService $itemService)
    {
        $result = $itemService->purchase($request->all());

        return response()->json($result, empty($result['success']) ? 400 : 200);
    }

    public function getAllPromotion(ItemService $itemService)
    {
        $promotion = $itemService->getPromotion();

        return response()->json([
            'success' => true,
            'promotion' => $promotion
        ]);
    }

    public function inventory(Request $request, ItemService $itemService)
    {
        $list = $itemService->getListInventory($request->user_id);

        return response()->json([
            'success' => true,
            'data' => $list
        ]);
    }
}

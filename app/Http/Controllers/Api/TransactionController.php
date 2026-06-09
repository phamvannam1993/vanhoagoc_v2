<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use App\Services\ItemService;
use Illuminate\Http\Request;

class TransactionController extends BaseModuleController
{
    public function getTransaction(Request $request, ItemService $itemService)
    {
        $list = $itemService->getListTransaction($request->user_id);

        return response()->json([
            'success' => true,
            'data' => $list
        ]);
    }
}

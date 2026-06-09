<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use App\Services\AppService;
use App\Services\BookService;
use Illuminate\Http\Request;

class DeepviewEditorController extends BaseModuleController
{
    public function getDeepview(Request $request, BookService $bookService)
    {
        $params = $request->all();

        $data = $bookService->getDeepview($params);

        if ($data['status']) {
            return response()->json($data['data']);
        } else {
            return response()->json([
                'result' => 'failed',
                'message' => $data['message']
            ]);
        }
    }
}

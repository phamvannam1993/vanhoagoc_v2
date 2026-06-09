<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use Illuminate\Support\Facades\Cache;
use App\Services\BookService;
use Illuminate\Http\Request;

class BookController extends BaseModuleController
{
    public function getBookAll(Request $request, BookService $bookService)
    {
        $params = $request->all();
        $data = $bookService->getBookAll($params);

        if (empty($data['status'])) {
            return response()->json([
                'result' => 'failed',
                'message' => $data['message']
            ]);
        } else {
            return response()->json([
                'result' => 'success',
                'data' => $data['data']
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use App\Repositories\PracticeImageRepository;
use Illuminate\Http\Request;

class PracticeController extends BaseModuleController
{
     public function __construct(
        private PracticeImageRepository $practiceImageRepository
    ){}

    public function getListImages($id, Request $request)
    {

        if(empty($id)) {
           return response()->json([
                'status' => true,
                'message' => 'Thành công!',
                'data' => []
            ]);
        }

        $data = $this->practiceImageRepository->getList(['practice_id' => $id]);

        return response()->json([
            'status' => true,
            'message' => 'Thành công!',
            'data' => $data
        ]);
    }
}

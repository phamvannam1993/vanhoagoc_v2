<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\BaseModuleController;
use App\Services\EventService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class EventController extends BaseModuleController
{
    public function __construct(
        private EventService $eventService
    ){}

    public function getList(Request $request)
    {
        $data = $this->eventService->getListApi($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Thành công!',
            'data' => $data
        ]);
    }

    public function detail($id, Request $request)
    {
        $data = $this->eventService->getDetailApi($id);

        if(empty($data)) {
            return response()->json([
                'status' => false,
                'message' => 'Event không tồn tại!',
                'data' => $data
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Thành công!',
            'data' => $data
        ]);
    }

    public function updatePoint(Request $request)
    {
        $data = $this->eventService->updatePoint($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật thành công!',
            'data' => $data
        ]);
    }

    public function getRanking(Request $request)
    {

        $data = $this->eventService->getRanking($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Thành công!',
            'data' => $data
        ]);
    }

    public function getUserRanking(Request $request)
    {

        $data = $this->eventService->getRanking($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Thành công!',
            'data' => data_get($data, '0', [])
        ]);
    }

    public function getUserDetail(Request $request)
    {
        try {
            $user = $this->eventService->getUserInfoByEvent($request->all());
        } catch (ModelNotFoundException $e) {
             return response()->json([
                'status' => false,
                'message' => 'Sự kiện không tồn tại',
                'data' => []
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Thành công!',
            'data' => $user
        ]);
    }

    public function getHighestRank(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required'
            ]);

            $data = $this->eventService->getHighestRank($request->all());

            return $this->successResponse($data);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validateErrorResponse($e->getMessage());
        } catch (\Exception $e) {
            Helper::logException($e);
            return $this->internalServerErrorResponse();
        }
    }
}

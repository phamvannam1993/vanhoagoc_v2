<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RealtimeEventService;
use Illuminate\Http\Request;

class RealtimeEventController extends Controller
{
    public function __construct(
        private RealtimeEventService $realtimeEventService
    ){}

    public function getList(Request $request)
    {
        $data = $this->realtimeEventService->getListApi($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Thành công!',
            'data' => $data
        ]);
    }

    public function detail($id, Request $request)
    {
        $data = $this->realtimeEventService->getDetailApi($id);

        if(empty($data)) {
            return response()->json([
                'status' => false,
                'message' => 'Sự kiện không tồn tại!',
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
        try {
            $data = $this->realtimeEventService->updatePoint($request->all());

            return response()->json([
                'status' => true,
                'message' => 'Cập nhật thành công!',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    public function getRanking(Request $request)
    {
        try {
            $data = $this->realtimeEventService->getRanking($request->all());

            return response()->json([
                'status' => true,
                'message' => 'Thành công!',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    public function getUserRanking(Request $request)
    {

        try {
            $data = $this->realtimeEventService->getRanking($request->all());

            return response()->json([
                'status' => true,
                'message' => 'Thành công!',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    public function invite(Request $request)
    {
        try {
            $params = $request->all();
            $params['status'] = \App\Enums\RealtimeEventUserConstant::STATUS_INVITING;
            $data = $this->realtimeEventService->inviteOrAcceptInvite($params);
            return response()->json([
                'status' => true,
                'message' => 'Gửi lời mời thành công!',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 200);
        }
    }

    public function acceptInvite(Request $request)
    {
        try {
            $params = $request->all();
            $params['status'] = \App\Enums\RealtimeEventUserConstant::STATUS_ACCEPTED;
            $data = $this->realtimeEventService->inviteOrAcceptInvite($params);

            return response()->json([
                'status' => true,
                'message' => 'Chấp nhận lời mời thành công!',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 200);
        }
    }

    public function kickUser(Request $request)
    {
        try {
            $data = $this->realtimeEventService->kickUser($request->all());
            return response()->json([
                'status' => true,
                'message' => 'Mời khỏi sự kiện thành công!',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 200);
        }
    }
}

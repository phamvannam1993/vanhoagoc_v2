<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\UserType;
use App\Services\AppService;
use App\Services\CommentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CommentController extends Controller
{
    protected $appService;
    protected $commentService;
    public function __construct(AppService $appService, CommentService $commentService)
    {
        $this->appService = $appService;
        $this->commentService = $commentService;
    }

    public function jsonList(Request $request, CommentService $commentService)
    {
        $list = $commentService->getList($request->all());

        return $list;
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Suggest/Index', [
            'query' => $request->query(),
        ]);
    }
    public function listCommentApp($id, Request $request): Response
    {
        $app = $this->appService->findAppById($id);

        $user = Auth::user();
        $canViewAll = in_array($user->userType->type, [UserType::TYPE_ADMIN, UserType::TYPE_DIRECTOR]);

        $app->load(['books' => function ($q) use ($user, $canViewAll) {
            if (!$canViewAll) {
                $q->where('user_id', $user->id);
            }
            $q->with('weeks');
        }]);

        // Collect allowed week IDs for teacher; null means no restriction (admin/director)
        $allowedWeekIds = null;
        if (!$canViewAll) {
            $allowedWeekIds = $app->books->flatMap(fn($b) => $b->weeks->pluck('id'))->values()->all();
        }

        return Inertia::render('Suggest/CommentApp', [
            'query' => $request->query(),
            'app' => $app,
            'allowedWeekIds' => $allowedWeekIds,
        ]);
    }

    public function delete($id)
    {
        $result = $this->commentService->delete($id);

        if (empty($result['status'])) {
            return response()->json([
                'status' => false,
                'messages' => $result['messages']
            ]);
        } else {
            return response()->json([
                'status' => true,
            ]);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $request->merge(['id' => $id]);
            $this->commentService->update($request->all());

            return response()->json([
                'status' => true,
                'message' => 'Cập nhật thành công!'
            ]);
        } catch (\Exception $ex) {
            Helper::logException($ex);

            return response()->json([
                'status' => false,
                'message' => 'Cập nhật thất bại!'
            ]);
        }
    }
}

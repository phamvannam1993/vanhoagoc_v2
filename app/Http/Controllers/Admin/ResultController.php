<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PointConstant;
use App\Enums\PointDayConstant;
use App\Http\Controllers\BaseModuleController;
use App\Models\App;
use App\Models\Classes;
use App\Models\Practice;
use App\Services\Admin\Class\ClassService;
use App\Services\AppService;
use App\Services\BookService;
use App\Services\PointService;
use App\Services\PracticeClassService;
use App\Services\PracticeService;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ResultController extends BaseModuleController
{
    protected $userService;
    protected $appService;
    protected $roleService;
    protected $classService;
    protected $practiceService;
    protected $bookService;
    protected $practiceClassService;

    public function __construct(UserService $userService, AppService $appService, RoleService $roleService, ClassService $classService,
                                PracticeService $practiceService, BookService $bookService, PracticeClassService $practiceClassService
    )
    {
        $this->userService = $userService;
        $this->appService = $appService;
        $this->roleService = $roleService;
        $this->classService = $classService;
        $this->practiceService = $practiceService;
        $this->bookService = $bookService;
        $this->practiceClassService = $practiceClassService;
    }
    public function postPractice(Request $request, PointService $pointService)
    {
        $params = $request->all();

        $pointService->save($params, PointConstant::POINT_PRACTICE);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thành công'
        ]);
    }

    public function getPractice(Request $request, PointService $pointService)
    {
        $data = $request->all();
        $data['type'] = PointConstant::POINT_PRACTICE;
        $list = $pointService->getListByType($data);

        return response()->json([
            'success' => true,
            'practices' => $list
        ]);
    }

    public function postTheoretical(Request $request, PointService $pointService)
    {
        $params = $request->all();

        $pointService->save($params, PointConstant::POINT_THEORETICAL);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thành công'
        ]);
    }

    public function getTheoretical(Request $request, PointService $pointService)
    {
        $data = $request->all();
        $data['type'] = PointConstant::POINT_THEORETICAL;
        $list = $pointService->getListByType($data);

        return response()->json([
            'success' => true,
            'theoreticals' => $list
        ]);
    }

    public function getResult(Request $request, PointService $pointService)
    {
        $data = $request->all();

        $result = $pointService->getSummaryPoint($data);


        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    public function getTotalTheoreticalPoint(Request $request, PointService $pointService)
    {
        $data = $request->all();
        $data['type'] = PointConstant::POINT_THEORETICAL;

        $result = $pointService->getSummaryPoint($data);

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    public function getListRank(Request $request, PointService $pointService)
    {
        $data = $request->all();
        if (empty($data['type'])) {
            $data['type'] = PointConstant::POINT_SUM;
        }

        $data = $pointService->getRank($data);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function getTotal(Request $request, PointService $pointService)
    {
        $params = $request->all();

        $total = $pointService->getTotalPoint($params);

        return response()->json([
            'success' => true,
            'total' => $total
        ], 200);
    }

    public function subTotal(Request $request, PointService $pointService)
    {
        $userId = $request->get('user_id');
        $type = PointDayConstant::POINT_HEART;

        $pointService->subTotalPoint($userId, $type);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thành công'
        ]);
    }

    public function getPracticePointDay(Request $request, PointService $pointService)
    {
        $params = $request->all();

        $data = $pointService->getPracticePointDay($params);

        return response()->json(["success" => true, "data" => $data], 200);
    }

    public function getResultPractice(Request $request): Response
    {
        $practiceId = $request->practice_id ?? null;
        $practice = Practice::with(['week', 'book'])->find($practiceId);

        return Inertia::render('Admin/Result/IndexByPractice', [
            'query' => $request->query(),
            'practice' => $practice
        ]);
    }

    public function getResultClass(Request $request): Response
    {
        $classId = $request->class_id ?? null;
        $classes = Classes::find($classId);

        return Inertia::render('Admin/Result/IndexByClass', [
            'query' => $request->query(),
            'classes' => $classes
        ]);
    }

    public function getResultSchool(Request $request): Response
    {
        $appId = $request->app_id ?? null;
        $app = App::find($appId);

        return Inertia::render('Admin/Result/IndexBySchool', [
            'query' => $request->query(),
            'app' => $app
        ]);
    }

    public function getResultByPractice(Request $request)
    {
        $practiceId = $request->practice_id ?? null;
        $classId = $request->class_id ?? null;

        if (!$practiceId || !$classId) {
            return response()->json([
                'status' => false,
            ]);
        }

        $classIds = [$classId];
        $studentIds = $this->classService->getStudentByClass($classIds)->pluck('user_id')->toArray();
        $listStudent = $this->userService->getListStudentResultPractice($studentIds, $practiceId);

        return response()->json([
            'status' => true,
            'data' => $listStudent
        ]);
    }

    public function getResultByClass(Request $request)
    {
        $classId = $request->class_id ?? null;
        $type = $request->type ?? null;

        if (!$classId) {
            return response()->json([
                'status' => false,
            ]);
        }

        $classIds = [$classId];
        $studentIds = $this->classService->getStudentByClass($classIds)->pluck('user_id')->toArray();
        $listUser = $this->userService->getListStudentResultClass($studentIds, $classId, $type);

        return response()->json([
            'status' => true,
            'data' => $listUser
        ]);
    }

    public function getResultBySchool(Request $request)
    {
        $appId = $request->app_id ?? null;
        $type = $request->type ?? null;

        if (!$appId) {
            return response()->json([
                'status' => false,
            ]);
        }

        $classIds = Classes::where('app_id', $appId)->get()->pluck('id')->toArray();
        $studentIds = $this->classService->getStudentByClass($classIds)->pluck('user_id')->toArray();
        $listUser = $this->userService->getListStudentResultSchool($studentIds, $type);

        return response()->json([
            'status' => true,
            'data' => $listUser
        ]);
    }
}

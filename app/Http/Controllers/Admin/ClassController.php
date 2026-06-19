<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ClassResultExport;
use App\Exports\RankAssignedTaskExport;
use App\Http\Controllers\Controller;
use App\Models\UserType;
use App\Models\App;
use App\Models\Book;
use App\Models\Classes;
use App\Models\Practice;
use App\Models\User;
use App\Models\Week;
use App\Models\Point;
use App\Models\PracticeClass;
use App\Services\PointService;
use App\Services\Admin\Class\ClassService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class ClassController extends Controller
{
    protected $classService;
    protected $pointService;
    protected $userService;

    public function __construct(ClassService $classService, PointService $pointService, UserService $userService)
    {
        $this->classService = $classService;
        $this->pointService = $pointService;
        $this->userService = $userService;
    }
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $appId = '';

        if ($user->userType?->type === UserType::TYPE_TEACHER) {
            $appId = $user->teacherApp->first()?->app_id;
        }
        $app_id = $appId ? $appId : $request->app_id;
        $appDetail = App::where('id', $app_id)->first();
        return Inertia::render('Admin/Class/Index', [
            'query' => $request->query(),
            'appId' => $appId,
            'app' => $appDetail
        ]);
    }

    public function getApps()
    {
        try {
            $apps = App::select('id', 'name')->get();
            return response()->json([
                'status' => true,
                'data' => $apps
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Admin/Class/Create', [
            'query' => $request->query()
        ]);
    }

    public function delete($id)
    {
        $user = Auth::user();
        try {
            Classes::where('id', $id)->delete();
            return response()->json([
                'status' => true
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => false,
            ]);
        }
    }

    public function jsonList(Request $request)
    {
        $params = $request->all();
        $list = $this->classService->getList($params);
        foreach($list as $item) {
            $point = Point::where('class_id', $item->id)->first();
            $item->is_result = false;
            if($point) {
                $item->is_result = true;
            }
        }
        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $this->classService->store($data);

        return response()->json([
            'status' => true,
            'data' => [
                'redirectUrl' => route('templates.index', ['appId' => $data['app_id']])
            ]
        ]);
    }

    public function edit(Request $request)
    {
        $data = $request->only(['app_id', 'id']);
        $record = $this->classService->detail($data);

        return Inertia::render('Admin/Class/Edit', [
            'query' => $request->query(),
            'record' => $record
        ]);
    }

    public function result(Request $request)
    {
        $data = $request->only(['app_id', 'id']);
        $record = $this->classService->detail($data);

        return Inertia::render('Admin/Result/Index', [
            'query' => $request->query(),
            'record' => $record
        ]);
    }

    public function assignment(Request $request)
    {
        $class_id = $request->class_id;
        $name_app = '';
        $name = '';
        if($class_id) {
            $classDetail = Classes::where('id', $class_id)->first();
            if (!$classDetail) {
                return redirect()->back()->with('error', 'Không tìm thấy lớp');
            }
            if(!empty($classDetail)) {
                $appDetail = App::where('id', $classDetail->app_id)->first();
                if(!empty($appDetail)) {
                    $name_app = $appDetail->name.' / '.$classDetail->name;
                }
            }
        } else {
            $user_id = $request->user_id;

            if (!$user_id) {
                return redirect()->route('admins.class.index')->with('error', 'Thiếu user_id');
            }

            $user = User::where('id', $user_id)->first();
            if (!$user) {
                return redirect()->route('admins.class.index')->with('error', "Học sinh ID {$user_id} không tồn tại");
            }

            $userClass = \App\Models\UserClass::where('user_id', $user_id)->first();
            if (!$userClass) {
                return redirect()->route('admins.class.index')->with('error', "Học sinh {$user->name} chưa được xếp vào lớp nào");
            }

            $classDetail = Classes::where('id', $userClass->class_id)->first();
            if (!$classDetail) {
                return redirect()->route('admins.class.index')->with('error', 'Không tìm thấy lớp của học sinh');
            }

            $appDetail = App::where('id', $classDetail->app_id)->first();
            if (!empty($appDetail)) {
                $name_app = $appDetail->name . ' / ' . $classDetail->name;
            }
            $name = $user->name;
        }

        return Inertia::render('Admin/Class/Assignment', [
            'query' => $request->query(),
            'name_app' => $name_app,
            'name' => $name,
            'app_id' => $classDetail->app_id ?? null
        ]);
    }

    public function rank(Request $request)
    {
        return Inertia::render('Admin/Rank/Index', [
            'query' => $request->query(),
        ]);
    }

    public function resultLearn(Request $request): RedirectResponse|Response
    {
        $class_id = $request->class_id;
        $classDetail = Classes::where('id', $class_id)->first();
        if (!$classDetail) {
            return redirect()->back()->with('error', 'Không tìm thấy lớp');
        }
        $name_app = '';
        if(!empty($classDetail)) {
            $appDetail = App::where('id', $classDetail->app_id)->first();
            if(!empty($appDetail)) {
                $name_app = $appDetail->name.' / '.$classDetail->name;
            }
        }
        $classDetail->load('app');
        return Inertia::render('Admin/Class/Result', [
            'query' => $request->query(),
            'name_app' => $name_app,
            'app_id' => $classDetail->app_id,
            'class' => $classDetail
        ]);
    }

    public function assignedTask(Request $request) {
        $classId = $request['class_id'];
        $userId = $request['user_id'];
        $data = $this->pointService->assignedTask($classId, $userId);
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function deleteAssign(Request $request) {
        $id = $request['id'];
        $practiceData = PracticeClass::with(['practice', 'book', 'week', 'class'])->where('id', $id)->first();
        if($practiceData) {
            PracticeClass::where('id', $id)->delete();
            Point::where('practice_id', $practiceData->practice->id)->where('class_id', $practiceData->class->class_id)->where('is_assign', 1)->delete();
        }
        return response()->json([
            'status' => true
        ]);
    }

    public function rankAssignedTask(Request $request) {
        $practice_id = $request->practice_id;
        $class_id = $request->class_id;
        $data = $this->pointService->rankAssignedTask($class_id,  $practice_id);
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function exportResult(Request $request)
    {
        $classId = $request->class_id;
        $tabId   = $request->tab_id ?? 2;
        $classDetail = Classes::with('app')->where('id', $classId)->firstOrFail();
        $appName   = $classDetail->app->name ?? '';
        $className = $classDetail->name;
        if ($tabId == 1) {
            $fileName = 'bao-cao-ket-qua-tu-hoc-' . \Str::slug($className) . '.docx';
        } else {
            $fileName = 'ket-qua-tong-hop-cac-nhiem-vu-lop-' . \Str::slug($className) . '.docx';
        }

        if ($tabId == 1) {
            $studentIds = $this->classService->getStudentByClass([$classId])->pluck('user_id')->toArray();
            $freeResult = $this->userService->getListStudentResultClass($studentIds, $classId, -1);
            $rows = collect($freeResult->items())->map(function ($student, $idx) {
                $arr = $student->toArray();
                $totalStar = collect($arr['best_points_per_practice'] ?? [])->sum('star_count');
                $totalTime = collect($arr['best_points_per_practice'] ?? [])->where('type', 1)->sum('time');
                $mm = floor($totalTime / 60); $ss = $totalTime % 60;
                return [
                    $idx + 1,
                    $arr['name'] ?? '',
                    $totalStar,
                    $totalTime > 0 ? sprintf('%02d:%02d', $mm, $ss) : '',
                ];
            })->values()->toArray();

            return (new \App\Exports\WordReportExport(
                'Báo cáo luyện tập tự do',
                ['STT', 'Họ và tên', 'Thành tích (Sao)', 'Thời gian làm'],
                $rows,
                [$className],
                $appName,
                [700, 4500, 1900, 1900],
                [0, 2, 3]
            ))->download($fileName);
        }

        $assignedTasks = $this->pointService->assignedTask($classId);
        $rows = collect($assignedTasks)->map(fn($t, $idx) => [
            $idx + 1,
            $t->name,
            $t->teacher_name ?? '',
            $t->duration,
            $t->progress_text ?? '',
            $t->submit_rate_text ?? '',
            $t->avg_point ?? '',
        ])->values()->toArray();

        return (new \App\Exports\WordReportExport(
            'Báo cáo nhiệm vụ được giao',
            ['STT', 'Danh sách nhiệm vụ', 'GV phụ trách', 'Thời hạn', 'Tiến độ', 'Tỉ lệ nộp bài', 'Điểm TB lớp'],
            $rows,
            [$className],
            $appName,
            [500, 3200, 1300, 1500, 800, 900, 800],
            [0, 4, 5, 6]
        ))->download($fileName);
    }

    public function exportRank(Request $request)
    {
        $classId = (int) $request->class_id;
        $practiceId = (int) $request->practice_id;

        $classDetail = Classes::with('app')->where('id', $classId)->firstOrFail();
        $practice = Practice::where('id', $practiceId)->firstOrFail();

        $week = Week::where('id', $practice->week_id)->first();
        $book = $week ? Book::where('id', $week->book_id)->first() : null;
        $practicePath = ($book ? $book->title . ' - ' : '') . ($week ? $week->name . ' - ' : '') . $practice->name;
        $appName = $classDetail->app->name ?? '';
        $className = $classDetail->name;

        $data = $this->pointService->rankAssignedTask($classId, $practiceId);
        $rows = collect($data)->map(fn($item) => [
            $item['rank'] ?? '',
            $item['name'] ?? '',
            $item['point_text'] ?? '',
            $item['correct_answers_text'] ?? '',
            $item['time_text'] ?? '',
        ])->toArray();

        $fileName = 'bang-xep-hang-nhiem-vu.docx';

        return (new \App\Exports\WordReportExport(
            'Bảng xếp hạng nhiệm vụ',
            ['Xếp hạng', 'Họ và tên', 'Điểm (Sao)', 'Số câu đúng', 'Thời gian làm'],
            $rows,
            [$practicePath, $className],
            $appName,
            [1200, 3500, 1500, 1500, 1500],
            [0, 2, 3, 4]
        ))->download($fileName);
    }

    public function showRank(Request $request): RedirectResponse|Response {
        $class_id = $request->class_id;
        $tab_id = $request->tab_id;
        $practice_id = $request->practice_id;
        $name = "";
        $appDetail = null;
        $classDetail = Classes::where('id', $class_id)->first();
        if (!$classDetail) {
            return redirect()->back()->with('error', 'Không tìm thấy lớp');
        }
        $name_app = '';
        if(!empty($classDetail)) {
            $appDetail = App::where('id', $classDetail->app_id)->first();
            if(!empty($appDetail)) {
                $name_app = $appDetail->name.' / '.$classDetail->name;
            } else {
                return redirect()->back()->with('error', 'Không tìm thấy đơn vị');
            }
        }
        $practice = Practice::where('id', $practice_id)->first();
        if(!$practice) {
            return redirect()->back()->with('error', 'Không tìm thấy người dùng');
        }
        $week = Week::where('id', $practice->week_id)->first();
        $book = $week ? Book::where('id', $week->book_id)->first() : null;
        $practicePath = ($book ? $book->title . ' - ' : '') . ($week ? $week->name . ' - ' : '') . $practice->name;
        $title = 'Bảng xếp hạng nhiệm vụ: ' . $practicePath;
        if($tab_id == 1) {
            $user_id = $request->user_id;
            $user = User::where('id', $user_id)->first();
            if(!$user) {
                return redirect()->back()->with('error', 'Không tìm thấy người dùng');
            }
            $title =  'Chi tiết luyện tập';
        }
        $classDetail->load('app');
        return Inertia::render('Admin/Class/Rank', [
            'query' => $request->query(),
            'name_app' => $name_app,
            'title' => $title,
            'name' => $name,
            'practice_id' => $practice_id,
            'app_id' => $classDetail->app_id,
            'class' => $classDetail
        ]);
    }
}

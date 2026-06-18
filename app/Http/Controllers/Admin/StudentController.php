<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PointDetailExport;
use App\Exports\StudentAssignedResultExport;
use App\Exports\StudentResultExport;
use App\Exports\UsersExport;
use App\Helpers\DynamoDbHelper;
use App\Http\Controllers\Controller;
use App\Imports\UsersImport;
use App\Models\App;
use App\Models\Book;
use App\Models\Classes;
use App\Models\Week;
use App\Models\Practice;
use App\Models\Question;
use App\Models\PointDetail;
use App\Models\UserClass;
use App\Models\PracticeClass;
use App\Models\User;
use App\Models\Point;
use App\Models\UserType;
use App\Models\ExerciseItem;
use App\Services\Admin\Class\ClassService;
use App\Services\AppService;
use App\Services\RoleService;
use App\Services\UserService;
use App\Services\StudentService;
use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StudentController extends Controller
{
    protected $userService;
    protected $appService;
    protected $roleService;
    protected $classService;
    protected $studentService;

    public function __construct(UserService $userService, AppService $appService, RoleService $roleService, ClassService $classService, StudentService $studentService)
    {
        $this->userService = $userService;
        $this->appService = $appService;
        $this->roleService = $roleService;
        $this->classService = $classService;
        $this->studentService = $studentService;
    }

    public function index(Request $request): Response
    {
        $class_id = $request->class_id;
        $classDetail = Classes::where('id', $class_id)->first();
        if(!empty($classDetail)){
            $classDetail->load('app');
        }
        return Inertia::render('Admin/Student/Index', [
            'query' => $request->query(),
            'class' => $classDetail,
        ]);
    }


    public function result(Request $request): RedirectResponse|Response
    {
        $userId = $request->user_id;
        $title = "Kết quả học tập";
        if($request->result_learn) {
            $title = "Chi tiết luyện tập";
        }
        $user = User::find($userId);
        if (!$user) {
            return redirect()->back()->with('error', 'Không tìm thấy người dùng');
        }
        $class_id = $request->class_id;
        $classDetail = Classes::where('id', $class_id)->first();
        $name_app = '';
        if(!empty($classDetail)) {
            $appDetail = App::where('id', $classDetail->app_id)->first();
            if(!empty($appDetail)) {
                $name_app = $appDetail->name.' / '.$classDetail->name;
            }
        }
        return Inertia::render('Admin/Student/Result', [
            'query' => $request->query(),
            'name' =>  $user->name,
            'name_app' => $name_app,
            'title' => $title
        ]);
    }
    public function create(Request $request): Response
    {
        $listApp = $this->appService->getListByRole();

        return Inertia::render('Admin/Student/Create', [
            'query' => $request->query(),
            'listApp' => $listApp
        ]);
    }

    public function edit(Request $request): Response
    {
        $user = $this->userService->detailStudent($request);
        $listApp = $this->appService->getListByRole();

        return Inertia::render('Admin/Student/Edit', [
            'query' => $request->query(),
            'listApp' => $listApp,
            'user' => $user
        ]);
    }

    public function jsonList(Request $request)
    {
        $params = $request->all();
        $user = Auth::user();

        if ($user->userType->type === UserType::TYPE_TEACHER && !$params['class_id']) {
           $class = Classes::where('user_id', $user->id)->get();
           $params['class_ids'] = $class->pluck('id');
        }

        $list = $this->userService->getListStudent($params);
        $userIds = $list->pluck('id')->toArray();
        $classId = $params['class_id'] ?? null;

        // Bulk load all data to avoid N+1 queries
        $userClasses = UserClass::whereIn('user_id', $userIds)
            ->with('classes.app')
            ->get()
            ->keyBy('user_id');

        $points = Point::whereIn('user_id', $userIds)
            ->select('user_id')
            ->distinct()
            ->get()
            ->keyBy('user_id');

        $practiceClassExists = [];
        if ($classId) {
            $practiceClassExists = PracticeClass::where('class_id', $classId)
                ->whereIn('student_id', $userIds)
                ->select('student_id')
                ->distinct()
                ->get()
                ->pluck('student_id')
                ->flip()
                ->toArray();
        }

        foreach($list as $item) {
            $item->app_name = "";
            $item->class_name = "";

            if (isset($userClasses[$item->id])) {
                $userClass = $userClasses[$item->id];
                if ($userClass->classes) {
                    $item->class_name = $userClass->classes->name;
                    if ($userClass->classes->app) {
                        $item->app_name = $userClass->classes->app->name;
                    }
                }
            }

            $item->is_result = isset($points[$item->id]);
            $item->is_assign = isset($practiceClassExists[$item->id]);
        }
        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function getClassByApp(Request $request)
    {
        $appId = $request->input('app_id');
        $user = auth()->user();

        if ($user->userType && $user->userType->type === \App\Models\UserType::TYPE_TEACHER) {
            $listClass = Classes::where('app_id', $appId)
                ->where('user_id', $user->id)
                ->get(['id', 'name']);
        } else {
            $listClass = $this->classService->getClassByApp($request->only(['app_id']));
        }

        return response()->json([
            'status' => true,
            'data' => $listClass
        ]);
    }

    public function resultSummaryByApp(Request $request)
    {
        try {
            $appId = $request->input('app_id');
            $classId = $request->input('class_id');
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 20);
            $search = $request->input('search', '');
            $user = auth()->user();

            // Build query like jsonList (without pagination)
            $query = User::query();
            $query = $query->with(['classes.app', 'studentApp', 'orders']);
            $query = $query->whereHas('userType', function ($q) {
                $q->where('type', UserType::TYPE_STUDENT_APP);
            });

            // Filter by director app
            if ($user->userType->type === UserType::TYPE_DIRECTOR) {
                $query = $query->whereHas('studentApp', function ($q) use($user) {
                    $q->where('app_id', $user->directorApp->id);
                });
            }

            // Filter by search
            if (!empty($search)) {
                $query->where(function($q) use($search) {
                    $q->where('name', 'like', '%'. $search . '%')
                        ->orWhere('email', 'like', '%'. $search . '%')
                        ->orWhere('username', 'like', '%'. $search . '%')
                        ->orWhere('tel', 'like', '%'. $search . '%');
                });
            }

            // Filter by app
            if ($appId) {
                $query = $query->whereHas('classes', function ($q) use ($appId) {
                    $q->where('classes.app_id', $appId);
                });
            }

            // Filter by class
            if ($classId) {
                $query = $query->whereHas('classes', function ($q) use ($classId) {
                    $q->where('classes.id', $classId);
                });
            }

            // Filter by teacher classes
            if ($user->userType->type === UserType::TYPE_TEACHER) {
                $query = $query->whereHas('classes', function ($q) use ($user) {
                    $q->where('classes.user_id', $user->id);
                });
            }

            // Paginate
            $list = $query->paginate($perPage, ['*'], 'page', $page);

            // Get user IDs for bulk loading
            $userIds = $list->pluck('id')->toArray();

            // Bulk load relationships for adding extra fields
            $userClasses = UserClass::whereIn('user_id', $userIds)
                ->with('classes.app')
                ->get()
                ->keyBy('user_id');

            // Get result summary from points
            $pointStats = Point::whereIn('user_id', $userIds)
                ->select('user_id', DB::raw('COUNT(*) as total_points'), DB::raw('AVG(star_count) as avg_score'))
                ->groupBy('user_id')
                ->get()
                ->keyBy('user_id');

            // Add extra fields like jsonList does
            foreach ($list as $item) {
                $item->app_name = '';
                $item->class_name = '';
                $item->total_points = 0;
                $item->total_score = 0;
                $item->is_result = false;

                if (isset($userClasses[$item->id])) {
                    $uc = $userClasses[$item->id];
                    if ($uc->classes) {
                        $item->class_name = $uc->classes->name;
                        if ($uc->classes->app) {
                            $item->app_name = $uc->classes->app->name;
                        }
                    }
                    $item->class_id = $uc->class_id;
                }

                if (isset($pointStats[$item->id])) {
                    $item->total_points = $pointStats[$item->id]->total_points ?? 0;
                    $item->total_score = round($pointStats[$item->id]->avg_score ?? 0, 1);
                    $item->is_result = true;
                }
            }

            return response()->json([
                'status' => true,
                'data' => $list
            ]);
        } catch (\Exception $e) {
            \Log::error('StudentController.resultSummaryByApp error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $data['user_type_id'] = UserType::where('type', UserType::TYPE_STUDENT_APP)->first()->id;
            DB::beginTransaction();
            $user = $this->userService->store($data);

            if ($user) {
                DB::table('users_classes')->where(['user_id' => $user->id])->delete();
                DB::table('users_classes')->insert(['user_id' => $user->id, 'class_id' => $data['class_id'] ?? null, 'app_id' => $data['app_id']]);
                $dataRole = [
                    'user_id' => $user->id,
                    'app_id' => 'p_' . $data['app_id'],
                    'type_id' => $data['user_type_id'],
                ];

                $this->roleService->assignRoleUser($dataRole);
            } else {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => true,
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            Log::info($exception->getLine());

            return response()->json([
                'status' => false,
            ]);
        }
    }

    public function assignClass(Request $request)
    {
        try {
            $data = $request->only(['user_id', 'app_id', 'class_id']);
            DB::table('users_classes')->where(['user_id' => $data['user_id'], 'app_id' => $data['app_id']])->update(['class_id' => $data['class_id']]);

            return response()->json([
                'status' => true,
            ]);
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());

            return response()->json([
                'status' => false,
            ]);
        }
    }

    public function deleteOrder($id) {
        try {
            Order::where('id', $id)->delete();
            return response()->json([
                'status' => true,
            ]);
        }  catch (\Exception $exception) {
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra trong quá trình xử lý'
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $user = User::where('id', $id)->with('classes')->firstOrFail();
            DB::beginTransaction();

            if ($user) {
                $params = [
                    'TableName' => 'cst.users',
                    'Key' => [
                        'user_id' => ['S' => $user->user_id_app],
                    ],
                ];
                $result = DynamoDbHelper::deleteItem($params);

                if ($result) {
                    if (!$user->classes->isEmpty()) {
                        $dataRole = [
                            'user_id' => $user->id,
                            'app_id' => 'p_' . $user->classes[0]->app_id,
                            'type_id' => $user->user_type_id,
                        ];
                        $this->roleService->deleteRole($dataRole);
                    }

                    DB::table('users_classes')->where(['user_id' => $user->id])->delete();
                    $this->userService->delete($id);
                } else {
                    return response()->json([
                        'status' => false,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());

            return response()->json([
                'status' => false,
            ]);
        }
    }

    public function deleteMultiple(Request $request)
    {
        try {
            $ids = $request->input('ids');
            $users = User::whereIn('id', $ids)->with('classes')->get();
            DB::beginTransaction();

            if (!empty($users)) {
                foreach ($users as $key => $user) {
                    $params = [
                        'TableName' => 'cst.users',
                        'Key' => [
                            'user_id' => ['S' => $user->user_id_app],
                        ],
                    ];
                    $result = DynamoDbHelper::deleteItem($params);
                    if (!$user->classes->isEmpty()) {
                        $dataRole = [
                            'user_id' => $user->id,
                            'app_id' => 'p_' . $user->classes[0]->app_id,
                            'type_id' => $user->user_type_id,
                        ];
                        $this->roleService->deleteRole($dataRole);
                    }

                    DB::table('users_classes')->where(['user_id' => $user->id])->delete();
                    $this->userService->delete($user->id);
                }
            }
            DB::commit();
            return response()->json([
                'status' => true
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());

            return response()->json([
                'status' => false,
            ]);
        }
    }

    public function postOrderMulti(Request $request) {
        $dataReq = $request->data;
        $user_ids = $dataReq['user_ids'];
        foreach($user_ids as $user_id) {
            $dataSave = [
                'user_id' => $user_id,
                'namesubject' => $dataReq['namesubject'],
                'price' =>  $dataReq['price']
            ];
            if($dataReq['namesubject']) {
                Order::create($dataSave);
            }
        }

        return response()->json(['success' => true, 'message' => 'Cập nhật thành công']);
    }

    public function resetPassword($id)
    {
        $user = $this->userService->findById($id);

        if (!$user) {
            return response()->json([
                'status' => false,
            ]);
        }

        $data['password'] = config('common.user.password_default');
        $this->userService->resetPassword($id);
        DynamoDbHelper::updateUserInfo($user->user_id_app, $data);

        return response()->json([
            'status' => true
        ]);
    }

    public function importStudent(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $import = new UsersImport();
        Excel::import($import, $request->file('file'));
        $classId = $request->class_id ?? null;
        $appId = $request->app_id ?? null;
        $userTypeId = UserType::where('type', UserType::TYPE_STUDENT_APP)->first()->id;
        $arrayStudent = [];
        try {
            DB::beginTransaction();
            foreach ($import->data as $key => $row) {

                $arrayStudent[$key]['name'] = $row['name'];
                $arrayStudent[$key]['username'] = $row['username'];
                $arrayStudent[$key]['tel'] = $row['phone'];
                $arrayStudent[$key]['address'] = $row['address'];
                $arrayStudent[$key]['email'] = $row['email'];
                $arrayStudent[$key]['birthday'] = isset($row['dob']) && $this->isValidDateFull($row['dob']) && trim($row['dob']) !== '' ? \DateTime::createFromFormat('d/m/Y', $row['dob'])->format('Y-m-d') : '';
                $arrayStudent[$key]['status'] = 'on';
                $arrayStudent[$key]['user_type_id'] = $userTypeId;

                $student = User::where('email', $row['email'])->first();
                if ($student) {
                    $userClass = UserClass::where('user_id', $student->id)->where('class_id', $classId)->first();
                    if(!$userClass) {
                        DB::table('users_classes')->insert(['user_id' => $student->id, 'class_id' => $classId ?? null, 'app_id' => $appId]);
                    }
                } else {
                    $newStudent = $this->userService->storeStudent($arrayStudent, $key);

                    if ($newStudent) {
                        DB::table('users_classes')->insert(['user_id' => $newStudent->id, 'class_id' => $classId ?? null, 'app_id' => $appId]);
                        $dataRole = [
                            'user_id' => $newStudent->id,
                            'app_id' => 'p_' . $appId,
                            'type_id' => $userTypeId,
                        ];

                        $this->roleService->assignRoleUser($dataRole);
                    }
                }
            }

            DB::commit();
            return response()->json([
                'status' => true
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());

            return response()->json([
                'status' => false
            ]);
        }
    }

    function isValidDateFull($dateStr) {
        if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateStr)) {
            return false;
        }

        $format = 'd/m/Y';
        $date = \DateTime::createFromFormat($format, $dateStr);

        return $date && $date->format($format) === $dateStr;
    }

    public function exportStudent(Request $request)
    {
        $classId = $request->get('class_id');

        return Excel::download(new UsersExport($classId), 'students.xlsx');
    }

    public function getUserPractice(Request $request)
    {
        $studentId = $request->user_id ?? null;
 
        // Try to find student by user_id_app first, then by ID
        $student = User::where('user_id_app', $studentId)->first();
        if (!$student && $studentId) {
            $student = $this->userService->findById($studentId);
        }

        if (!$student || !$student->id) {
            return response()->json([
                'status' => false,
                'message' => 'Student not found'
            ]);
        }

        $userClasses = UserClass::where('user_id', $student->id)->get();
        $userClass = $userClasses->first();

        $data = [
            'status' => $student->status,
            'name' => $student->name,
            'class_id' => $userClass ? $userClass->class_id : null,
            'class_ids' => $userClasses->pluck('class_id'),
            'codeAppid' => $userClass ? $userClass->app_id : null,
            'who_create' => $student->created_by,
            'createdAt' => Carbon::parse($student->created_at)->timestamp,
            'updatedAt' => Carbon::parse($student->updated_at)->timestamp,
            'year' => $student->created_at->year
        ];

        // Get all practices in student's class first
        $classId = $student?->classes->first()->id ?? null;
        $exerciseItemsByPractice = [];

        if ($classId && $student && $student->id) {
            // Get exercise items in one optimized query using raw SQL
            $exerciseItemsByPractice = DB::table('assignment_students')
                ->join('exercise_assignments', 'assignment_students.exercise_assignment_id', '=', 'exercise_assignments.id')
                ->join('exercise_items', 'exercise_assignments.exercise_item_id', '=', 'exercise_items.id')
                ->where('assignment_students.student_id', $student->id)
                ->select('exercise_items.practice_id', 'exercise_items.id')
                ->get()
                ->groupBy('practice_id')
                ->map(fn($items) => $items->pluck('id')->values()->toArray())
                ->toArray();
        }

        // Get all practices in student's class - use minimal select to reduce data
        if ($classId) {
            $allPractices = PracticeClass::with(['practice' => fn($q) => $q->select('id', 'practice_id'),
                                                  'book' => fn($q) => $q->select('id', 'bo_sach', 'lop', 'name'),
                                                  'week' => fn($q) => $q->select('id', 'week_id'),
                                                  'class' => fn($q) => $q->select('id')])
                ->where('class_id', $classId)
                ->select('id', 'practice_id', 'book_id', 'week_id', 'from', 'to', 'student_id')
                ->get();

            // Basic map function for week_unlock_struct (no practices array)
            $mapItemBasic = function($item) {
                return [
                    'practice_id'      => ($item->book?->bo_sach ?? '') . '.' . ($item->book?->lop ?? '') . '.' . ($item->book?->name ?? '') . '.quyen1.' . ($item->week?->week_id ?? 0) . '.' . ($item->practice?->practice_id ?? 0),
                    'time'             => Carbon::parse($item->from)->timestamp,
                    'timeout'          => Carbon::parse($item->to)->timestamp,
                    'practice_id_tool' => $item->practice_id,
                ];
            };

            // week_unlock_struct: class-level assignments (student_id is null)
            $data['week_unlock_struct'] = $allPractices
                ->filter(fn($item) => is_null($item->student_id))
                ->map($mapItemBasic)
                ->values();

            // Extended map function for week_unlock_struct_personal (with practices array)
            $mapItemWithPractices = function($item) use ($exerciseItemsByPractice) {
                $practiceId = $item->practice_id;
                $mapped = [
                    'practice_id'      => ($item->book?->bo_sach ?? '') . '.' . ($item->book?->lop ?? '') . '.' . ($item->book?->name ?? '') . '.quyen1.' . ($item->week?->week_id ?? 0) . '.' . ($item->practice?->practice_id ?? 0),
                    'time'             => Carbon::parse($item->from)->timestamp,
                    'timeout'          => Carbon::parse($item->to)->timestamp,
                    'practice_id_tool' => $item->practice_id,
                ];

                // Add exercise item IDs if this practice has assignments to this student
                if (isset($exerciseItemsByPractice[$practiceId])) {
                    $mapped['exercise_item_ids'] = $exerciseItemsByPractice[$practiceId];

                    // Add practices array with exercise items data
                    $exerciseItems = ExerciseItem::whereIn('id', $exerciseItemsByPractice[$practiceId])
                        ->with(['practice' => fn($q) => $q->select('id', 'practice_id', 'name', 'img', 'status', 'lesson_video', 'lesson_noi', 'lesson_doc', 'lesson_doc2', 'pdf', 'avatar', 'setting_advance')])
                        ->select('id', 'name', 'practice_id', 'level', 'order')
                        ->get();

                    $practicesArray = [];
                    foreach ($exerciseItems as $exerciseItem) {
                        $practice = $exerciseItem->practice;

                        if ($practice) {
                            $settingAdvance = $practice->setting_advance ? json_decode($practice->setting_advance, true) : null;
                            $background = $settingAdvance ? json_decode($settingAdvance['background'] ?? null) : null;
                            $colorNotPractice = $settingAdvance ? json_decode($settingAdvance['color_not_practice'] ?? null) : null;
                            $colorDonePractice = $settingAdvance ? json_decode($settingAdvance['color_done_practice'] ?? null) : null;

                            $isLessonDocText = $practice->lesson_doc && !preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $practice->lesson_doc);
                            $isLessonDoc2Text = $practice->lesson_doc2 && !preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $practice->lesson_doc2);

                            $practicesArray[] = [
                                // Practice (bài cha) info
                                'practice_id' => $mapped['practice_id'],
                                'practice_id_tool' => $mapped['practice_id_tool'],
                                'time' => $mapped['time'],
                                'timeout' => $mapped['timeout'],

                                // Exercise item (bài con) info
                                'exercise_id' => $exerciseItem->id,
                                'exercise_name' => $exerciseItem->name,
                                'cover_image' => $practice->img ? \App\Helpers\Helper::getCloudFront($practice->img) : '',
                                'name' => $exerciseItem->name,
                                'status' => $practice->status ?? 'on',
                                'lessons' => [
                                    'lesson_video' => $practice->lesson_video ? \App\Helpers\MediaHelper::getCorrectValueByType('video', $practice->lesson_video) : '',
                                    'lesson_noi' => $practice->lesson_noi ? \App\Helpers\MediaHelper::getCorrectValueByType('audio', $practice->lesson_noi) : '',
                                    'lesson_doc' => $practice->lesson_doc ?? '',
                                    'lesson_doc2' => $practice->lesson_doc2 ?? '',
                                    'lesson_vr' => '',
                                    'lesson_pdf' => $practice->pdf ? \App\Helpers\Helper::getCloudFront($practice->pdf) : null,
                                    'avatar' => $practice->avatar ? \App\Helpers\Helper::getCloudFront($practice->avatar) : null,
                                    'background' => $background,
                                    'color_not_practice' => $colorNotPractice,
                                    'color_done_practice' => $colorDonePractice,
                                    'is_lesson_doc_text' => $isLessonDocText,
                                    'is_lesson_doc2_text' => $isLessonDoc2Text,
                                    'taptrung' => ($practice->taptrung ?? 'false') === 'true',
                                ],
                                'tem_playables' => []
                            ];
                        }
                    }

                    $mapped['practices'] = $practicesArray;
                }

                return $mapped;
            };

            // week_unlock_struct_personal: personal exercise item assignments (from new system)
            // Include all practices that have exercise item assignments for this student
            $personalPracticeIds = array_keys($exerciseItemsByPractice);

            $data['week_unlock_struct_personal'] = $allPractices
                ->filter(fn($item) => in_array($item->practice_id, $personalPracticeIds))
                ->map($mapItemWithPractices)
                ->values();
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function assignedTask(Request $request) {
        $classId = $request->class_id;
        $userId = $request->user_id;
        $params = [
            'user_id' => $userId,
            'class_id' => $classId
        ];
        return $this->studentService->assignedTask($params);
    }

    public function exportStudentResult(Request $request)
    {
        $userId  = $request->user_id;
        $classId = $request->class_id;
        $tabId   = $request->tab_id ?? 1;

        $user        = User::find($userId);
        $classDetail = Classes::with('app')->where('id', $classId)->first();

        $studentName = $user?->name ?? '';
        $appName     = $classDetail?->app?->name ?? '';
        $className   = $classDetail?->name ?? '';

        if ($tabId == 2) {
            $params = ['user_id' => $userId, 'class_id' => $classId];
            $jsonResponse = $this->studentService->assignedTask($params);
            $raw = $jsonResponse->getData(true)['data'] ?? [];

            $rows = collect($raw)->map(function ($r, $idx) {
                $r = is_array($r) ? $r : (array) $r;
                $status = data_get($r, 'status');
                $review = data_get($r, 'review_status');
                return [
                    $idx + 1,
                    data_get($r, 'name', ''),
                    data_get($r, 'duration', ''),
                    $status ? 'Đã thực hiện' : 'Chưa thực hiện',
                    data_get($r, 'point_text', ''),
                    data_get($r, 'time_text', ''),
                    data_get($r, 'rank', ''),
                    $review == 1 ? 'Đạt' : ($review == 2 ? 'Không đạt' : 'Chưa đánh giá'),
                ];
            })->values()->toArray();

            $fileName = 'nhiem-vu-duoc-giao-' . \Str::slug($studentName) . '.docx';
            return (new \App\Exports\WordReportExport(
                'Báo cáo nhiệm vụ được giao',
                ['STT', 'Nội dung', 'Thời hạn', 'Trạng thái', 'Điểm (Sao)', 'Thời gian', 'Xếp hạng', 'Đánh giá'],
                $rows,
                ['Học sinh: ' . $studentName, $className],
                $appName,
                [500, 2400, 1300, 1100, 900, 900, 900, 1000],
                [0, 3, 4, 5, 6, 7]
            ))->download($fileName);
        }

        $params = [
            'user_id'   => $userId,
            'class_id'  => $classId,
            'is_assign' => 2,
            'type'      => 1,
        ];
        $jsonResponse = $this->studentService->getFreePractice($params);
        $raw = $jsonResponse->getData(true)['data'] ?? [];

        $rows = collect($raw)->map(fn($r, $idx) => [
            $idx + 1,
            data_get($r, 'day_create', ''),
            data_get($r, 'name', ''),
            data_get($r, 'point_text_2', ''),
            data_get($r, 'point_text_1', ''),
            data_get($r, 'time_text', ''),
            data_get($r, 'total_point', ''),
            data_get($r, 'rank', ''),
        ])->values()->toArray();

        $fileName = 'chi-tiet-luyen-tap-tu-do-' . \Str::slug($studentName) . '.docx';
        return (new \App\Exports\WordReportExport(
            'Báo cáo luyện tập tự do',
            ['STT', 'Ngày', 'Nội dung', 'Điểm lý thuyết', 'Điểm luyện tập', 'Thời gian', 'Điểm tổng hợp', 'Xếp hạng'],
            $rows,
            ['Học sinh: ' . $studentName, $className],
            $appName,
            [500, 1100, 2300, 1100, 1100, 900, 1100, 900],
            [0, 1, 3, 4, 5, 6, 7]
        ))->download($fileName);
    }

    public function exportPointDetail(Request $request)
    {
        $pointId = $request->point_id;
        $userId  = $request->user_id;

        $user  = User::find($userId);
        $point = Point::find($pointId);
        if (!$point || !$user) {
            abort(404);
        }

        $practice = Practice::find($point->practice_id);
        $week     = $practice ? Week::find($practice->week_id) : null;
        $book     = $week ? Book::find($week->book_id) : null;
        $title    = ($book ? $book->title . ' - ' : '') . ($week ? $week->name . ' - ' : '') . ($practice?->name ?? '');

        $classDetail = Classes::with('app')->find($request->class_id);
        $nameApp = ($classDetail?->app?->name ?? '') . ' / ' . ($classDetail?->name ?? '');

        $details = PointDetail::where('point_id', $pointId)->get();
        $rows = $details->map(function ($item, $idx) {
            $seconds = (int) $item->time;
            $minutes = floor($seconds / 60);
            $secs    = $seconds % 60;
            $timeText = $seconds > 0 ? sprintf('%02d:%02d', $minutes, $secs) : '';
            $result = $item->star_count == 1 ? 'Chính xác' : ($timeText ? 'Chưa chính xác' : 'Chưa thực hiện');
            return [$idx + 1, $result, $timeText];
        })->values()->toArray();

        // Summary
        $totalPoint   = (int) $point->star_count;
        $totalTimeSec = (int) $point->time;
        $h = floor($totalTimeSec / 3600);
        $m = floor(($totalTimeSec % 3600) / 60);
        $s = $totalTimeSec % 60;
        $totalTimeText = $totalTimeSec > 0
            ? ($h > 0 ? sprintf('%02d:%02d:%02d', $h, $m, $s) : sprintf('%02d:%02d', $m, $s))
            : '';

        if ((int) $point->is_assign === 1) {
            $rankList = collect(app(\App\Services\PointService::class)->rankAssignedTask($request->class_id, $point->practice_id));
            $self = $rankList->firstWhere('user_id', (int) $userId);
            $submitted = $rankList->filter(fn($r) => ($r['point_id'] ?? 0) > 0)->count();
            $rankText = ($self && ($self['rank'] ?? null) && $submitted > 0) ? $self['rank'] . '/' . $submitted : '';
        } else {
            $userPoint = \App\Models\UserRankPractice::where('user_id', $userId)
                ->where('class_id', $request->class_id)
                ->where('practice_id', $point->practice_id)
                ->value('total_point');
            $rankText = '';
            if ($userPoint !== null) {
                $betterCount = \App\Models\UserRankPractice::where('class_id', $request->class_id)
                    ->where('practice_id', $point->practice_id)
                    ->where('total_point', '>', $userPoint)
                    ->count();
                $totalUser = UserClass::where('class_id', $request->class_id)->count();
                $rankText = ($betterCount + 1) . '/' . $totalUser;
            }
        }

        // First row carries summary values; columns 3,4,5 will be vertically merged across all rows
        $rowsWithSummary = [];
        foreach ($rows as $i => $r) {
            $rowsWithSummary[] = $i === 0
                ? array_merge($r, [$totalPoint, $totalTimeText, $rankText])
                : array_merge($r, ['', '', '']);
        }

        $fileName = 'bang-chi-tiet-' . \Str::slug($practice?->name ?? 'bai') . '.docx';

        return (new \App\Exports\WordReportExport(
            'Bảng chi tiết thực hiện nhiệm vụ',
            ['Câu', 'Kết quả', 'Thời gian', 'Tổng điểm', 'Tổng thời gian', 'Xếp hạng'],
            $rowsWithSummary,
            [$title, 'Học sinh: ' . ($user->name ?? '')],
            $nameApp,
            [600, 2600, 1200, 1300, 1500, 1300],
            [0, 1, 2, 3, 4, 5],
            [3, 4, 5]
        ))->download($fileName);
    }

    public function freePractice(Request $request) {
        $classId = $request->class_id;
        $userId = $request->user_id;
        $params = [
            'user_id' => $userId,
            'class_id' => $classId,
            'is_assign' => 2,
            'type' => 1
        ];
        return $this->studentService->getFreePractice($params);
    }

    public function pointDetail(Request $request) : RedirectResponse|Response
    {
        $class_id = $request->class_id;
        $classDetail = Classes::where('id', $class_id)->first();
        $name_app = '';
        if(!empty($classDetail)) {
            $appDetail = App::where('id', $classDetail->app_id)->first();
            if(!empty($appDetail)) {
                $name_app = $appDetail->name.' / '.$classDetail->name;
            }
        }
        $userId = $request->user_id;
        $pointId =  $request->point_id;
        $data = [
            'title' => '',
        ];
        $user = User::find($userId);
        $params = [
            'user_id' => $userId,
            'id' => $pointId
        ];
        $point = Point::where($params)->first();
        if (!$user || !$point) {
            return redirect()->back()->with('error', 'Không tìm thấy thông tin');
        }
        $title = '';
        $practice = Practice::where('id', $point->practice_id)->first();
        if (!$practice) {
            return redirect()->back()->with('error', 'Không tìm thấy thông tin');
        }
        $week = Week::where('id', $practice->week_id)->first();
        $book = $week ? Book::where('id', $week->book_id)->first() : null;
        $practicePath = ($book ? $book->title . ' - ' : '') . ($week ? $week->name . ' - ' : '') . $practice->name;

        $pointDetails = PointDetail::where('point_id', $point->id)->get();
        foreach($pointDetails as $item) {
            $question = Question::where('id', $item->question_id)->first();
            $item->name = !empty($question) ? $question->name : '';
            $seconds = (int)$item->time;

            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $secs = $seconds % 60;

            if ($hours > 0) {
                $formatted = sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
            } else {
                $formatted = sprintf("%02d:%02d", $minutes, $secs);
            }
            $item->time_text = $seconds > 0 ? $formatted : '';
        }

        $title = 'Bảng chi tiết thực hiện nhiệm vụ: ' . $practicePath;
        if ($request->practice_id) {
            $title = 'Chi tiết nhiệm vụ: ' . $practicePath;
        }
        if ($request->result_learn) {
            $title = 'Chi tiết kết quả luyện tập: ' . $practicePath;
        }

        $totalPoint = (int) $point->star_count;
        $totalTimeSec = (int) $point->time;
        $h = floor($totalTimeSec / 3600);
        $m = floor(($totalTimeSec % 3600) / 60);
        $s = $totalTimeSec % 60;
        $totalTimeText = $totalTimeSec > 0
            ? ($h > 0 ? sprintf('%02d:%02d:%02d', $h, $m, $s) : sprintf('%02d:%02d', $m, $s))
            : '';

        // Rank: use BXH for assigned tasks; UserRankPractice for free practice
        if ((int) $point->is_assign === 1) {
            $rankList = collect(app(\App\Services\PointService::class)->rankAssignedTask($class_id, $point->practice_id));
            $self = $rankList->firstWhere('user_id', (int) $userId);
            $submitted = $rankList->filter(fn($r) => ($r['point_id'] ?? 0) > 0)->count();
            $rank = $self['rank'] ?? null;
            $rankText = ($rank && $submitted > 0) ? $rank . '/' . $submitted : '';
        } else {
            $userPoint = \App\Models\UserRankPractice::where('user_id', $userId)
                ->where('class_id', $class_id)
                ->where('practice_id', $point->practice_id)
                ->value('total_point');
            $rankText = '';
            if ($userPoint !== null) {
                $betterCount = \App\Models\UserRankPractice::where('class_id', $class_id)
                    ->where('practice_id', $point->practice_id)
                    ->where('total_point', '>', $userPoint)
                    ->count();
                $totalUser = UserClass::where('class_id', $class_id)->count();
                $rankText = ($betterCount + 1) . '/' . $totalUser;
            }
        }

        $summary = [
            'total_point'     => $totalPoint,
            'total_time_text' => $totalTimeText,
            'rank_text'       => $rankText,
        ];

        return Inertia::render('Admin/Student/PointDetail', [
            'query' => $request->query(),
            'data' => $data,
            'title' => $title,
            'pointDetails' => $pointDetails,
            'name_app' => $name_app,
            'name' => $user->name,
            'summary' => $summary,
        ]);
    }
}

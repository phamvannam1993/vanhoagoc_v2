<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Classes;
use App\Models\User;
use App\Models\UserType;
use App\Services\Admin\Class\ClassService;
use App\Services\AppService;
use App\Services\BookService;
use App\Services\ExerciseAssignmentService;
use App\Services\PracticeClassService;
use App\Services\PracticeService;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class PracticeController extends Controller
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

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Teacher/AssignPractice', [
            'query'      => $request->query(),
            'student_id' => $request->student_id,
            'student_name' => $request->student_id
                ? \App\Models\User::find($request->student_id)?->name
                : null,
        ]);
    }

    public function getLesson(Request $request)
    {
        $classId = $request->class_id;
        $studentId = $request->student_id;
        $userId = Auth::user()->id;

        // If student_id is provided, get app and class from student's profile
        if ($studentId) {
            $student = User::find($studentId);
            if (!$student) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy học sinh'
                ]);
            }
            // Get student's app and class from their user classes
            $userClass = \App\Models\UserClass::where('user_id', $studentId)->first();
            if (!$userClass) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy app của học sinh'
                ]);
            }
            $appId = $userClass->app_id;
            // Get class_id from student's user_class, or use current user's class if not set
            if (!$classId) {
                $classId = $userClass->class_id;
                // Fallback: use current user's class if student doesn't have one
                if (!$classId) {
                    $currentUserClass = Classes::where('user_id', $userId)->first();
                    if ($currentUserClass) {
                        $classId = $currentUserClass->id;
                    }
                }
            }
        } else {
            // Otherwise get from current user's class
            $class = Classes::where('user_id', $userId)->first();
            if (!$class) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy lớp học'
                ]);
            }
            $appId = $class->app_id;
            // Get class_id from current user's class
            if (!$classId) {
                $classId = $class->id;
            }
        }

        // If still no class_id, return error
        if (!$classId) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy class_id. Vui lòng gán học sinh vào một lớp'
            ]);
        }

        $listLesson = $this->bookService->getLessonByApp($appId, $userId, $classId);

        // Convert to array and add class_id for frontend use
        $lessonArray = $listLesson->toArray();

        return response()->json([
            'status' => true,
            'data' => $lessonArray,
            'class_id' => $classId  // Return class_id separately for frontend
        ]);
    }

    public function assignPractice(Request $request)
    {
        $user = Auth::user();
        $data = $request->all();
        $data['user_id'] = $user->id;
        $data['student_id'] = $request->student_id ?? null;
        $result = $this->practiceClassService->store($data);

        if ($result) {
            return response()->json([
                'status' => true,
            ]);
        }

        return response()->json([
            'status' => false,
        ]);
    }

    public function withDrawPractice(Request $request)
    {
        $user = Auth::user();
        $practiceId = $request->practice_id;
        $classId = $request->class_id;
        $studentId = $request->student_id ?? null;
        $result = $this->practiceClassService->deleteById($user->id, $practiceId, $classId, $studentId);

        if ($result) {
            return response()->json([
                'status' => true,
            ]);
        }

        return response()->json([
            'status' => false,
        ]);
    }

    public function getExerciseItems(Request $request)
    {
        try {
            $practiceId = $request->get('practice_id');
            $studentId = $request->get('student_id');

            if (!$practiceId) {
                return response()->json([
                    'status' => false,
                    'message' => 'practice_id là bắt buộc'
                ], 400);
            }

            $items = \App\Models\ExerciseItem::where('practice_id', $practiceId)
                ->withCount('questionEditors')
                ->orderBy('order')
                ->get();

            // Get assigned item IDs if student_id provided
            $assignedItemIds = [];
            if ($studentId) {
                $assignedItemIds = \App\Models\AssignmentStudent::where('student_id', $studentId)
                    ->join('exercise_assignments', 'assignment_students.exercise_assignment_id', '=', 'exercise_assignments.id')
                    ->whereIn('exercise_assignments.exercise_item_id', $items->pluck('id'))
                    ->pluck('exercise_assignments.exercise_item_id')
                    ->toArray();
            }

            $result = $items->map(function($item) use ($assignedItemIds) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'level' => $item->level,
                    'order' => $item->order,
                    'total_questions' => $item->question_editors_count,
                    'is_assigned' => in_array($item->id, $assignedItemIds),
                ];
            });

            Log::info('Exercise items fetched', [
                'practice_id' => $practiceId,
                'count' => count($result),
                'student_id' => $studentId,
            ]);

            return response()->json([
                'status' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting exercise items: ' . $e->getMessage(), [
                'practice_id' => $request->get('practice_id'),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function assignExerciseItems(Request $request, ExerciseAssignmentService $assignmentService)
    {
        try {
            $validated = $request->validate([
                'practice_id' => 'required|integer',
                'student_id' => 'required|integer',
                'exercise_item_ids' => 'required|array',
                'exercise_item_ids.*' => 'integer',
                'checkedTime' => 'boolean',
                'checkedNonTime' => 'boolean',
                'from' => 'nullable|date_format:Y/m/d',
                'to' => 'nullable|date_format:Y/m/d',
            ]);

            $result = $assignmentService->assignToStudent($validated);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error assigning exercise items: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Lỗi giao bài tập con: ' . $e->getMessage()
            ], 500);
        }
    }

    public function withDrawPractice(Request $request)
    {
        $user = Auth::user();
        $practiceId = $request->practice_id;
        $classId = $request->class_id;
        $studentId = $request->student_id ?? null;
        $result = $this->practiceClassService->deleteById($user->id, $practiceId, $classId, $studentId);

        if ($result) {
            return response()->json([
                'status' => true,
            ]);
        }

        return response()->json([
            'status' => false,
        ]);
    }

    public function withdrawExerciseItem(Request $request, ExerciseAssignmentService $assignmentService)
    {
        try {
            $validated = $request->validate([
                'exercise_item_id' => 'required|integer',
                'student_id' => 'required|integer',
            ]);

            $result = $assignmentService->withdrawFromStudent(
                $validated['exercise_item_id'],
                $validated['student_id']
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error withdrawing exercise item: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Lỗi hủy giao bài tập con: ' . $e->getMessage()
            ], 500);
        }
    }
}

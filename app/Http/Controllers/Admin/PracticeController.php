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
        $userId = Auth::user()->id;
        $class = Classes::where('user_id', $userId)->first();
        $appId = $class->app_id;
        $classId = $request->class_id;
        $listLesson = $this->bookService->getLessonByApp($appId, $userId, $classId);

        return response()->json([
            'status' => true,
            'data' => $listLesson
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
}

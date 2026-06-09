<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Book;
use App\Models\Classes;
use App\Models\Course;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Models\UserType;
use App\Services\Admin\Class\ClassService;
use App\Services\AppService;
use App\Services\BookService;
use App\Services\CourseService;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class TeacherController extends Controller
{
    protected $userService;
    protected $appService;
    protected $roleService;
    protected $classService;
    protected $courseService;

    public function __construct(
        UserService $userService,
        AppService $appService,
        RoleService $roleService,
        ClassService $classService,
        CourseService $courseService
    ) {
        $this->userService = $userService;
        $this->appService = $appService;
        $this->roleService = $roleService;
        $this->classService = $classService;
        $this->courseService = $courseService;
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Teacher/Index', [
            'query' => $request->query()
        ]);
    }

    public function create(Request $request): Response
    {
        $listApp = $this->appService->getListByRole();
        
        return Inertia::render('Admin/Teacher/Create', [
            'query' => $request->query(),
            'listApp' => $listApp
        ]);
    }

    public function edit(Request $request): Response
    {
        $user = $this->userService->detailTeacher($request);
        $listApp = $this->appService->getListByRole();

        return Inertia::render('Admin/Teacher/Edit', [
            'query' => $request->query(),
            'listApp' => $listApp,
            'user' => $user
        ]);
    }

    public function jsonList(Request $request)
    {
        $params = $request->all();
        $list = $this->userService->getListTeacher($params);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function getClassByApp(Request $request)
    {
        $appId = $request->only(['app_id']);
        $listClass = $this->classService->getClassByApp($appId);

        return response()->json([
            'status' => true,
            'data' => $listClass
        ]);
    }

    public function getCourseByApp(Request $request)
    {
        $appId = $request->input('app_id');
        $paginator = app(BookService::class)->getList(['app_id' => $appId]);
        $listCourse = $paginator->getCollection()->map(fn($b) => ['id' => $b->id, 'name' => $b->title ?: $b->name])->values();

        return response()->json([
            'status' => true,
            'data' => $listCourse
        ]);
    }

    public function getEventByApp(Request $request)
    {
        $appId = $request->input('app_id');
        $list = Event::where('app_id', $appId)
            ->orderBy('start_datetime', 'DESC')
            ->get(['id', 'name'])
            ->map(fn($e) => ['id' => $e->id, 'name' => $e->name]);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();

            if (empty($data['app_id'])) {
                return response()->json([
                    'status' => false,
                    'messages' => ['app_id' => 'Vui lòng chọn App.'],
                ]);
            }

            $data['user_type_id'] = UserType::where('type', UserType::TYPE_TEACHER)->first()->id;
            DB::beginTransaction();
            $user = $this->userService->store($data);

            if ($user) {
                // Phân quyền Lớp/Phòng ban
                Classes::where('user_id', $user->id)->update(['user_id' => null]);
                Classes::whereIn('id', $data['class_id'])->update(['user_id' => $user->id]);

                // Phân quyền Môn học/Khóa học (book)
                Book::where('user_id', $user->id)->update(['user_id' => null]);
                if (!empty($data['course_id'])) {
                    Book::whereIn('id', $data['course_id'])->update(['user_id' => $user->id]);
                }

                $isUpdate = !empty($data['id']);

                // Role p_ cho App (quản lý lớp)
                $roleApp = [
                    'user_id' => $user->id,
                    'app_id'  => 'p_' . $data['app_id'],
                    'type_id' => $data['user_type_id'],
                ];

                // Role b_ cho App (phân quyền môn học/khóa học)
                $roleCourse = [
                    'user_id' => $user->id,
                    'app_id'  => 'b_' . $data['app_id'],
                    'type_id' => $data['user_type_id'],
                ];

                if ($isUpdate) {
                    // Xóa tất cả role cũ (p_, b_, s_) rồi tạo lại
                    $this->roleService->reassignRoleUser($roleApp);
                    $this->roleService->assignRoleUser($roleCourse);
                } else {
                    $this->roleService->assignRoleUser($roleApp);
                    $this->roleService->assignRoleUser($roleCourse);
                }

                // Phân quyền sự kiện s_{event_id}
                Role::where('user_id', $user->id)
                    ->where('permission', 'like', 's_%')
                    ->delete();
                foreach ($data['event_ids'] ?? [] as $eventId) {
                    Role::updateOrCreate(
                        ['user_id' => $user->id, 'permission' => 's_' . $eventId],
                        ['user_type_id' => $data['user_type_id']]
                    );
                }
            }
            DB::commit();
            return response()->json([
                'status' => true,
            ]);
        } catch (\Illuminate\Database\QueryException $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());

            $message = 'Có lỗi xảy ra, vui lòng thử lại.';
            if ($exception->errorInfo[1] === 1062) {
                $message = 'Email hoặc tên tài khoản đã tồn tại.';
            }

            return response()->json([
                'status' => false,
                'messages' => ['email' => $message],
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());

            return response()->json([
                'status' => false,
                'messages' => ['email' => 'Có lỗi xảy ra, vui lòng thử lại.'],
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $user = User::where('id', $id)->firstOrFail();

            DB::beginTransaction();
            if ($user) {
                // Xóa toàn bộ roles của user (không chỉ first()) để tránh role leak
                $this->roleService->deleteRoleDirector($user->id);

                Classes::where(['user_id' => $user->id])->update(['user_id' => null]);
                $this->userService->delete($id);
            }
            DB::commit();
            return response()->json([
                'status' => true,
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
            $users = User::whereIn('id', $ids)->get();

            DB::beginTransaction();
            if (!empty($users)) {
                foreach ($users as $key => $user) {
                    // Xóa toàn bộ roles của user để tránh role leak
                    $this->roleService->deleteRoleDirector($user->id);

                    Classes::where(['user_id' => $user->id])->update(['user_id' => null]);
                    $this->userService->delete($user->id);
                }
            }
            DB::commit();
            return response()->json([
                'status' => true,
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());

            return response()->json([
                'status' => false,
            ]);
        }
    }

    public function resetPassword($id)
    {
        $user = $this->userService->findById($id);

        if (!$user) {
            return response()->json([
                'status' => false,
            ]);
        }

        $this->userService->resetPassword($id);

        return response()->json([
            'status' => true
        ]);
    }
}

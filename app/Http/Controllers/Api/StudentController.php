<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use App\Services\ActiveCodeService;
use App\Services\RoleService;
use App\Services\StudentService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentController extends BaseModuleController
{
    protected $userService;
    protected $roleService;
    protected $activeCodeService;

    public function __construct(
        UserService $userService,
        RoleService $roleService,
        ActiveCodeService $activeCodeService,
        private StudentService $studentService)
    {
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->activeCodeService = $activeCodeService;
    }

    public function create(Request $request)
    {
        $student = $this->__createStudent($request->all());

        if (!empty($student)) {
            return response()->json([
                'status' => true,
                'data' => $student
            ]);
        }else {
            return response()->json([
                'status' => false,
                'data' => null
            ]);
        }
    }

    public function createWithCode(Request $request)
    {
        $student = $this->__createStudent($request->all());
        $codes = $this->activeCodeService->generateCode(1);

        if (!empty($student)) {
            return response()->json([
                'status' => true,
                'data' => [
                    'student' => $student,
                    'code' => data_get($codes[0], 'code')
                ]
            ]);
        }else {
            return response()->json([
                'status' => false,
                'data' => null
            ]);
        }
    }

    public function getAssignment(Request $request)
    {
        $userId = $request->user_id;
        $params = [
            'user_id' => $userId,
        ];
        $data = $this->studentService->getAssignment($params);
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function getAssignmentDetail(Request $request)
    {
        $data = $this->studentService->getAssignmentDetail($request->all());
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    private function __createStudent($params)
    {
        try {
            DB::beginTransaction();
            $user = $this->userService->store($params);

            if ($user) {
                DB::table('users_classes')->where(['user_id' => $user->id])->delete();
                DB::table('users_classes')->insert(['user_id' => $user->id, 'class_id' => $params['class_id'] ?? null, 'app_id' => $params['app_id']]);

                $this->roleService->assignRoleUser([
                    'user_id' => $user->id,
                    'app_id' => 'p_' . $params['app_id'],
                    'type_id' => $params['user_type_id'],
                ]);
                $user->email = 'user_'.$user->id;
                $user->save();
            } else {
                DB::rollBack();
                return;
            }

            DB::commit();
            return $user;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            Log::info($exception->getLine());
        }
    }
}

<?php

namespace App\Services;

use App\Models\UserType;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    private $userRepository;

    public function __construct(
        UserRepository $userRepository
    )
    {
        $this->userRepository = $userRepository;
    }

    public function login($data)
    {
        \Log::info('AuthService.login called', [
            'email' => $data['email'] ?? null,
            'role_type' => $data['role_type'] ?? null,
            'all_data_keys' => array_keys($data)
        ]);

        $record = $this->userRepository->first([
            'email' => $data['email']
        ]);

        if (empty($record->id)) {
            return [
                'status' => false,
                'message' => [
                    'email' => 'email không tồn tại'
                ]
            ];
        } elseif (!Hash::check($data['password'], $record->password)) {
            return [
                'status' => false,
                'message' => [
                    'password' => 'mật khẩu không đúng'
                ]
            ];
        }

        // Check role match if role_type provided
        Log::info('AuthService.login - checking role', [
            'role_type_from_form' => $data['role_type'] ?? 'NOT_SET',
            'user_id' => $record->id,
            'user_type_id' => $record->user_type_id,
            'user_type_obj' => $record->userType ? $record->userType->type : 'NULL'
        ]);

        if (isset($data['role_type']) && !empty($data['role_type'])) {
            $userType = $record->userType->type ?? null;
            $selectedRole = $this->mapRoleTypeToUserType($data['role_type']);

            Log::info('AuthService - role validation', [
                'user_actual_role' => $userType,
                'selected_role' => $selectedRole,
                'match' => $userType === $selectedRole,
                'role_mapping' => [
                    'input' => $data['role_type'],
                    'mapped' => $selectedRole
                ]
            ]);

            if ($userType !== $selectedRole) {
                Log::warning('AuthService - role mismatch detected', [
                    'user_id' => $record->id,
                    'expected' => $selectedRole,
                    'actual' => $userType
                ]);

                return [
                    'status' => false,
                    'message' => [
                        'email' => 'tài khoản này không có vai trò ' . $this->getRoleLabel($data['role_type'])
                    ]
                ];
            }
        }

        Auth::login($record);
        return [
            'status' => true,
            'isAdmin' => $record->user_type_id == 1,
            'userType' => $record->userType
        ];
    }

    private function mapRoleTypeToUserType($roleType)
    {
        $mapping = [
            'admin' => UserType::TYPE_ADMIN,
            'editor' => UserType::TYPE_EDITOR,
            'director' => UserType::TYPE_DIRECTOR,
            'teacher' => UserType::TYPE_TEACHER,
        ];
        return $mapping[$roleType] ?? null;
    }

    private function getRoleLabel($roleType)
    {
        $labels = [
            'admin' => 'Quản trị hệ thống',
            'editor' => 'Biên tập viên',
            'director' => 'Quản trị trường',
            'teacher' => 'Giáo viên',
        ];
        return $labels[$roleType] ?? '';
    }

    public function changePassword($data)
    {
        $user = auth()->user();

        if (Hash::check($data['current_password'], $user->password)) {
            $user->password = Hash::make($data['password']);
            $user->save();

            return true;
        } else {
            return false;
        }
    }
}

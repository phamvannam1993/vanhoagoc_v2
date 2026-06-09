<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
        $record = $this->userRepository->first([
            'email' => $data['email']
        ]);

        if (empty($record->id)) {
            return [
                'status' => false,
                'message' => [
                    'email' => 'email khong ton tai'
                ]
            ];
        } elseif (!Hash::check($data['password'], $record->password)) {
            return [
                'status' => false,
                'message' => [
                    'password' => 'password khong dung'
                ]
            ];
        } else {
            Auth::login($record);
            return [
                'status' => true,
                'isAdmin' => $record->user_type_id == 1,
                'userType' => $record->userType
            ];
        }
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

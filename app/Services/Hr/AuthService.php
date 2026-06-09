<?php

namespace App\Services\Hr;

use App\Repositories\HumanResourceRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    private $humanResourceRepository;

    public function __construct(
        HumanResourceRepository $humanResourceRepository
    ) {
        $this->humanResourceRepository = $humanResourceRepository;
    }

    public function login($data)
    {
        $record = $this->humanResourceRepository->first([
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
            Auth::guard('hr')->login($record);

            return [
                'status' => true,
                'isAdmin' => $record->user_type_id == 1
            ];
        }
    }
}

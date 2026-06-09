<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Str;

class SocialService
{
    private $userRepository;

    public function __construct(
        UserRepository $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    public function login($data)
    {
        $password = Str::random(8);
        $data['password'] = bcrypt($password);
        $data['user_type_id'] = 6;
        $data['status'] = 'on';
        $user_id_app =  md5(uniqid(mt_rand(), true));
        $user = $this->userRepository->first(['email' => $data['email']]);

        if ($user && $user->user_id_app == '') {
            return ['success' => false, 'message' => 'Email này đã được đăng ký'];
        }

        if ($user) {
            $user_id_app = $user->user_id_app;
        } else {
            $data['user_id_app'] = $user_id_app;
            $this->userRepository->create($data);
        }

        return [
            'success' => true,
            'message' => 'Đăng ký thành công thành công',
            'user_id' => $user_id_app
        ];
    }
}

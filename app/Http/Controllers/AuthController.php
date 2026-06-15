<?php

namespace App\Http\Controllers;

use App\Models\UserType;
use App\Services\AuthService;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showFormLogin()
    {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    }

    public function login(Request $request, AuthService $authService)
    {
        $data = $request->all([
            'email',
            'password'
        ]);
        $data['email'] = isset($data['email']) ? strtolower($data['email']) : '';
        $result = $authService->login($data);

        if (empty($result['status'])) {
            return back()->withErrors($result['message']);
        }

        // Trang mặc định theo vai trò
        $default = route('apps.dashboard');
        if (!$result['isAdmin'] && $result['userType']
            && $result['userType']->type !== UserType::TYPE_ADMIN
            && $result['userType']->type !== UserType::TYPE_EDITOR) {
            $default = $result['userType']->type === UserType::TYPE_DIRECTOR
                ? route('admins.school.index')
                : route('admins.class.index');
        }

        // Nếu trước đó người dùng bấm vào 1 chức năng cần quyền (đã lưu url.intended
        // lúc đăng xuất), quay lại đúng chức năng đó; nếu không thì về trang mặc định.
        return redirect()->intended($default);
    }

    public function logout(Request $request)
    {
        // Lưu URL đích để sau khi đăng nhập bằng tài khoản có quyền sẽ quay lại đúng chức năng.
        // (logout chỉ gọi Auth::logout(), không invalidate session nên dữ liệu này còn nguyên)
        if ($request->filled('intended')) {
            session(['url.intended' => $request->input('intended')]);
        }

        Auth::logout();

        return redirect(route('showFormLogin'));
    }
}

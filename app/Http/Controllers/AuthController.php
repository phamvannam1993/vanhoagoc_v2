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
        \Log::info('Login attempt', [
            'email' => $request->input('email'),
            'intended' => $request->input('intended')
        ]);

        // Lưu intended URL nếu có (từ modal login)
        if ($request->filled('intended')) {
            session(['url.intended' => $request->input('intended')]);
        }

        $data = $request->all([
            'email',
            'password',
            'role_type'
        ]);
        $data['email'] = isset($data['email']) ? strtolower($data['email']) : '';
        $result = $authService->login($data);

        \Log::info('Login result', [
            'status' => $result['status'] ?? false,
            'message' => $result['message'] ?? ''
        ]);

        if (empty($result['status'])) {
            // Nếu là request từ modal (có intended parameter), return JSON error
            if ($request->filled('intended')) {
                return response()->json([
                    'message' => $result['message'] ?? 'Đăng nhập thất bại'
                ], 422);
            }
            return back()->withErrors(['message' => $result['message']]);
        }

        // Check quyền truy cập dựa vào user type
        $userType = $result['userType']->type ?? null;

        // Admin/Editor/Director/Teacher → allow /admins access
        $allowAdmins = in_array($userType, [
            UserType::TYPE_ADMIN,
            UserType::TYPE_EDITOR,
            UserType::TYPE_DIRECTOR,
            UserType::TYPE_TEACHER
        ]) || $result['isAdmin'];

        // Trang mặc định theo vai trò
        $default = route('apps.dashboard');

        if ($allowAdmins && $userType === UserType::TYPE_DIRECTOR) {
            $default = route('admins.school.index');
        } elseif ($allowAdmins && $userType === UserType::TYPE_TEACHER) {
            $default = route('admins.class.index');
        }

        // Nếu là request từ modal (có intended parameter), return JSON response
        // Frontend sẽ handle redirect
        if ($request->filled('intended')) {
            $intendedUrl = session()->pull('url.intended') ?? $request->input('intended');
            return response()->json([
                'status' => true,
                'redirect' => $intendedUrl ?? $default
            ]);
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

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
        } else {
            if ($result['isAdmin']) {
                return redirect(route('apps.dashboard'));
            } else if ($result['userType'] && ($result['userType']->type !== UserType::TYPE_ADMIN && $result['userType']->type !== UserType::TYPE_EDITOR )) {
                if ($result['userType']->type === UserType::TYPE_DIRECTOR) {
                    return redirect(route('admins.school.index'));
                } else {
                    return redirect(route('admins.class.index'));
                }
            } else {
                return redirect(route('apps.dashboard'));
            }
        }
    }

    public function logout()
    {
        Auth::logout();

        return redirect(route('showFormLogin'));
    }
}

<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\BaseModuleController;
use App\Services\Hr\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthHrController extends BaseModuleController
{
    public function showFormLogin()
    {
        return Inertia::render('Hr/Auth/Login');
    }

    public function login(Request $request, AuthService $authService)
    {
        $data = $request->all([
            'email',
            'password'
        ]);

        $result = $authService->login($data);

        if (empty($result['status'])) {
            return back()->withErrors($result['message']);
        } else {
            return redirect(route('hr.point.index'));
        }
    }

    public function logout()
    {
        Auth::guard('hr')->logout();

        return redirect(route('hr.showFormLogin'));
    }
}

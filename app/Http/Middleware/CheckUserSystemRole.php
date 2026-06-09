<?php

namespace App\Http\Middleware;

use App\Enums\UserTypeConstant;
use App\Models\UserType;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class CheckUserSystemRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        // Đang chờ confirm có loại tài khoản trong hệ thống nào khác có quyền vào thao tác không
        if ($user->userType->type !== UserType::TYPE_ADMIN && $user->userType->type !== UserType::TYPE_EDITOR && $user->userType->type !== UserType::TYPE_TEACHER && $user->userType->type !== UserType::TYPE_DIRECTOR) {
            return redirect()->route('403');
        }

        return $next($request);
    }
}

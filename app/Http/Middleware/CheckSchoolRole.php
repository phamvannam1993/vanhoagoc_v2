<?php

namespace App\Http\Middleware;

use App\Enums\UserTypeConstant;
use App\Models\UserType;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class CheckSchoolRole
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

        // Allow json.list endpoint for all authenticated users
        if (str_contains($request->path(), 'school/json/list')) {
            return $next($request);
        }

        // For other routes, only allow ADMIN and DIRECTOR
        if ($user->userType->type !== UserType::TYPE_ADMIN && $user->userType->type !== UserType::TYPE_DIRECTOR) {
            return redirect()->route('403');
        }

        return $next($request);
    }
}

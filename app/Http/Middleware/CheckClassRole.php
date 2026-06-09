<?php

namespace App\Http\Middleware;

use App\Enums\UserTypeConstant;
use App\Models\UserType;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class CheckClassRole
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

        if ($user->userType->type !== UserType::TYPE_ADMIN && $user->userType->type !== UserType::TYPE_DIRECTOR && $user->userType->type !== UserType::TYPE_TEACHER) {
            return redirect()->route('403');
        }

        if ($user->userType->type === UserType::TYPE_TEACHER) {
            $notAllowActions = [
                'App\Http\Controllers\Admin\ClassController@create'
            ];
            $action = Route::currentRouteAction();
            if (in_array($action, $notAllowActions)) {
                return redirect()->route('403');
            }
        }


        return $next($request);
    }
}

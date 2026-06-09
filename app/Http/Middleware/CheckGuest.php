<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;

class CheckGuest
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
        $user = auth('hr')->user();

        if (!empty($user->id)) {
            $name = Route::currentRouteName();
            $list = explode('.', $name);
            if (!empty($list[0]) && $list[0] == 'hr') {
                return redirect('/hr/point');
            }
        }

        return $next($request);
    }
}

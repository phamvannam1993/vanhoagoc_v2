<?php

namespace App\Http\Middleware;

use App\Enums\UserTypeConstant;
use App\Services\UserAccessModuleService;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class CheckUserRole
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
        
        if ($user->user_type_id == UserTypeConstant::TYPE_EDITOR || $user->user_type_id == UserTypeConstant::TYPE_TEACHER || $user->user_type_id == UserTypeConstant::TYPE_TEACHER_ADMIN) {
            $name = Route::currentRouteName();

            $accountRoutes = [
                'users.index',
                'users.member',
                'users.newMember',
                'users.editMember',
                'users.assignRole'
            ];

            if (in_array($name, $accountRoutes)) {
                return redirect(route('users.updateAccount'));
            }
            if ($this->checkAccessModule($request)) {
                return $next($request);
            }
            $appRoutes = [
                'apps.edit',
                'apps.detail'
            ];
           
            if (in_array($name, $appRoutes)) {
                $appId = $request->id;
            } else {
                $appId = $request->get('app_id');

                if (empty($appId)) {
                    $appId = $request->get('appId');
                }
            }
            if (!empty($appId)) {
                $record = DB::table('role')
                    ->where('user_id', $user->id)
                    ->where(function($query) use ($appId) {
                        $query->where('permission', $appId)
                            ->orWhere('permission', 'p_' . $appId)
                            ->orWhere('permission', 'b_' . $appId);
                    })
                    ->first();

                if (empty($record->id)) {
                    return redirect(route('apps.dashboard'));
                }
            }
        }

        return $next($request);
    }

    public function checkAccessModule($request)
    {
        $appId = $request->get('app_id');

        if (empty($appId)) {
            $appId = $request->get('appId');
        }

        [$appIds, $parentAppIds] = app(UserAccessModuleService::class)->getAppIds();
        if (in_array($appId, array_merge($appIds, $parentAppIds))) {
            return true;
        }
        return false;
    }
}

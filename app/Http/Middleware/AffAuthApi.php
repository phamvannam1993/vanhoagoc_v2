<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;

class AffAuthApi
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
        $key = $request->header('X-PRIVATE-KEY');

        if ($key !== env('PRIVATE_API_KEY')) {
            return response()->json(['status' => false, 'error' => 'Unauthorized'], 401);
        }
        $user = User::where('id', $request->get('login_user_id', env('MASTER_USER_ID')))->first();

        auth()->login($user);

        if (!empty($request['user_id'])) {
            $user = User::where(function($q) use($request){
                $q->where('user_id_app', $request['user_id'])
                    ->orWhere(DB::raw('BINARY id'), $request['user_id']);
            })->first();

            if (!empty($user)) {
                $request['user_id'] = $user->id;
            }else {
                return response()->json(['success' => false, 'message' => 'User không tồn tại']);
            }
        }

        return $next($request);
    }
}

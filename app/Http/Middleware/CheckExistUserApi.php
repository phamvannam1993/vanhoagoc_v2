<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckExistUserApi
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
        if (!isset($request['user_id']) || $request['user_id'] == '') {
            return response()->json(['success' => false, 'message' => 'user_id không được để trống']);
        }
        $user = DB::table('users')->where(function($q) use($request){
            $q->where('user_id_app', $request['user_id'])
                ->orWhere(DB::raw('BINARY id'), $request['user_id']);
        })->first();

        if (!$user) {
            if (!$request->isMethod('post')) {
                return response()->json(['success' => false, 'message' => 'user_id không tồn tại']);
            }
            $dataSave = [];
            $password = Str::random(8);
            $dataSave['password'] = bcrypt($password);
            $dataSave['user_id_app'] = $request['user_id'];
            $dataSave['user_type_id'] = 6;
            $dataSave['status'] = 'on';
            $id = DB::table('users')->insertGetId($dataSave);
            $request['user_id'] = $id;
        } else {
            $request['user_id'] =  $user->id;
        }

        return $next($request);
    }
}

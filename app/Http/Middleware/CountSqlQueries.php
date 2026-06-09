<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class CountSqlQueries
{
    protected int $queryCount = 0;

    public function handle($request, Closure $next)
    {
        // Đếm số query
        DB::listen(function () {
            $this->queryCount++;
        });

        $response = $next($request);

        if (!app()->environment(['production', 'staging', 'local'])) {
            return $response;
        }

        // Lấy method, URL, params
        $method = $request->method();
        $url = $request->fullUrl();
        $params = $request->all();
        $ip = $request->ip();

        // Ghi log
        Log::info('[SQL Count] [IP: '.$ip.']', [
            'method' => $method,
            'url' => $url,
            'params' => $params,
            'query_count' => $this->queryCount,
            'need_to_check' => $this->queryCount >= 50
        ]);

        return $response;
    }
}

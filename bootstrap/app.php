<?php

use App\Http\Middleware\AffAuthApi;
use App\Http\Middleware\CheckExistUserApi;
use App\Http\Middleware\CheckGuest;
use App\Http\Middleware\CheckUserRole;
use App\Http\Middleware\CheckSchoolRole;
use App\Http\Middleware\CheckAdminRole;
use App\Http\Middleware\CheckClassRole;
use App\Http\Middleware\CheckUserSystemRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\CountSqlQueries::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\CountSqlQueries::class,
        ]);

        $middleware->alias([
            'checkExitUser' => CheckExistUserApi::class,
            'checkUserRole' => CheckUserRole::class,
            'CheckGuest' => CheckGuest::class,
            'checkSchoolRole' => CheckSchoolRole::class,
            'checkAdminRole' => CheckAdminRole::class,
            'checkClassRole' => CheckClassRole::class,
            'checkUserSystemRole' => CheckUserSystemRole::class,
            'affAuthApi' => AffAuthApi::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            return redirect($request->url());
        });
    })->create();

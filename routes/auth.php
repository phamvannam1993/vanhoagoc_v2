<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showFormLogin'])->name('showFormLogin');
    Route::post('login', [AuthController::class, 'login'])->name('login');
});

Route::middleware('auth')->group(function () {
    Route::any('/logout', [AuthController::class, 'logout'])->name('logout');
});

<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showFormLogin'])->name('showFormLogin');
});

// POST login không có middleware guest - cho phép user đã login dùng modal login để đổi tài khoản
Route::post('login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth')->group(function () {
    Route::any('/logout', [AuthController::class, 'logout'])->name('logout');
});

<?php

use App\Modules\Auth\Http\Controllers\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'loginForm'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'logout'])->name('logout');
});

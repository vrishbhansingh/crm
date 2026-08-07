<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| User Authentication Routes
|--------------------------------------------------------------------------
|
| Login/logout intentionally stay under /admin and /user — two doors to
| the identical login page (Phase 1). Everything else that used to live
| here (Profile, Security, Order Management, Quotation, Invoice) has
| moved to the unified /-prefixed routes in routes/app.php
| (increments 1-2) — Orders (Group B) was the last piece.
|
*/

Route::get('/login', [AuthController::class, 'login'])->name('user.login');
Route::get('/', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'login_submit'])
    ->middleware('throttle:5,1')
    ->name('user.login_submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('user.logout');

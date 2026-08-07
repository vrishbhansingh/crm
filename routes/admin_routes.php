<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Every route below now authenticates through the single unified
| AuthController/`web` guard (Phase 1). Login/logout intentionally stay
| under /admin and /user — two doors to the identical login page
| (Phase 1), not navigation anyone lands on mid-session. Everything that
| used to live here has moved to the unified /-prefixed routes in
| routes/app.php (increments 1-2) — Orders (Group B) was the last piece.
|
*/

Route::get('/login', [AuthController::class, 'login'])->name('admin.login');
Route::get('/', [AuthController::class, 'login']);
Route::post('/login-submit', [AuthController::class, 'login_submit'])
    ->middleware('throttle:5,1')
    ->name('admin.login_submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

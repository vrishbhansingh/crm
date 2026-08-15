<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| User Authentication Routes
|--------------------------------------------------------------------------
|
| Canonical /login, /logout. No /user prefix (routes/web.php's own '/'
| already serves the same login page, so the duplicate root route that
| used to live here was removed rather than left to collide with it).
|
*/

Route::get('/login', [AuthController::class, 'login'])->name('user.login');
Route::post('/login', [AuthController::class, 'login_submit'])
    ->middleware('throttle:5,1')
    ->name('user.login_submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('user.logout');

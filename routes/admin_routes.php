<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Only /admin/logout survives here — every sidebar/header logout form still
| submits to it by that name. The /admin/login + /admin/login-submit
| duplicate of the canonical /login page was removed: sign-in only happens
| at /login (regular) or /superadmin/login (Super Admin). Everything else
| that used to live here has moved to the unified /-prefixed routes in
| routes/app.php.
|
*/

Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

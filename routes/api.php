<?php

use App\Http\Controllers\Api\V1\CrmController;
use App\Http\Middleware\EnsureActiveApiUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->middleware(['auth:sanctum', EnsureActiveApiUser::class])->group(function () {
    Route::get('/leads', [CrmController::class, 'leads']);
    Route::post('/leads', [CrmController::class, 'storeLead']);
    Route::get('/deals', [CrmController::class, 'deals']);
    Route::get('/companies', [CrmController::class, 'companies']);
    Route::get('/contacts', [CrmController::class, 'contacts']);
    Route::get('/tasks', [CrmController::class, 'tasks']);
    Route::post('/tasks', [CrmController::class, 'storeTask']);
});

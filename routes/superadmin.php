<?php

use App\Http\Controllers\Admin\PlatformAuditLogController;
use App\Http\Controllers\Admin\PlatformDashboardController;
use App\Http\Controllers\Admin\PlatformMailSettingsController;
use App\Http\Controllers\Admin\PlatformUserController;
use App\Http\Controllers\Admin\TenantBackupController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Auth\SuperAdminForgotPasswordController;
use App\Http\Controllers\SuperAdminAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [SuperAdminAuthController::class, 'login'])->name('login');
Route::post('/login', [SuperAdminAuthController::class, 'authenticate'])->middleware('throttle:5,1')->name('login.submit');

Route::middleware('throttle:5,1')->group(function () {
    Route::get('/forgot-password', [SuperAdminForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [SuperAdminForgotPasswordController::class, 'send'])->name('password.email');
    Route::get('/reset-password/{token}', [SuperAdminForgotPasswordController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [SuperAdminForgotPasswordController::class, 'update'])->name('password.update');
});

Route::middleware(['admin_middle', 'role:Super Admin'])->group(function () {
    Route::get('/', [PlatformDashboardController::class, 'index'])->name('dashboard');
    Route::get('/audit-log', [PlatformAuditLogController::class, 'index'])->name('audit.index');
    Route::get('/settings/mail', [PlatformMailSettingsController::class, 'index'])->name('settings.mail.edit');
    Route::get('/settings/mail/form', [PlatformMailSettingsController::class, 'form'])->name('settings.mail.form');
    Route::put('/settings/mail', [PlatformMailSettingsController::class, 'update'])->name('settings.mail.update');
    Route::delete('/settings/mail', [PlatformMailSettingsController::class, 'destroy'])->name('settings.mail.destroy');
    Route::post('/settings/mail/test', [PlatformMailSettingsController::class, 'test'])->name('settings.mail.test');
    Route::get('/companies', [TenantController::class, 'index'])->name('tenants.index');
    Route::post('/companies', [TenantController::class, 'store'])->name('tenants.store');
    Route::put('/companies/{tenant}', [TenantController::class, 'update'])->name('tenants.update')->whereNumber('tenant');
    Route::delete('/companies/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy')->whereNumber('tenant');
    Route::post('/companies/{tenant}/approve', [TenantController::class, 'approve'])->name('tenants.approve')->whereNumber('tenant');
    Route::post('/companies/{tenant}/reject', [TenantController::class, 'reject'])->name('tenants.reject')->whereNumber('tenant');
    Route::post('/companies/{tenant}/provision', [TenantController::class, 'provision'])->name('tenants.provision')->whereNumber('tenant');
    Route::post('/companies/{tenant}/health', [TenantController::class, 'health'])->name('tenants.health')->whereNumber('tenant');
    Route::get('/companies/{tenant}/users', [PlatformUserController::class, 'index'])->name('users.index')->whereNumber('tenant');
    Route::post('/companies/{tenant}/users', [PlatformUserController::class, 'store'])->name('users.store')->whereNumber('tenant');
    Route::put('/companies/{tenant}/users/{user}', [PlatformUserController::class, 'update'])->name('users.update')->whereNumber(['tenant', 'user']);
    Route::post('/companies/{tenant}/transfer-admin', [PlatformUserController::class, 'transferAdmin'])->name('users.transfer_admin')->whereNumber('tenant');
    Route::get('/backups', [TenantBackupController::class, 'index'])->name('backups.index');
    Route::get('/backups/{filename}/download', [TenantBackupController::class, 'download'])->name('backups.download');
    Route::delete('/backups/{filename}', [TenantBackupController::class, 'destroy'])->name('backups.destroy');
    Route::post('/logout', [SuperAdminAuthController::class, 'logout'])->name('logout');
});

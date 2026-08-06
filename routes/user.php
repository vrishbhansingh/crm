<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\User\DashController;
use App\Http\Controllers\user\InvoiceController;
use App\Http\Controllers\user\LeadListController;
use App\Http\Controllers\User\LoginController;
use App\Http\Controllers\user\OrderPaymentController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\user\QuotationController;
use App\Http\Controllers\user\SecurityController;

/*
|--------------------------------------------------------------------------
| User Authentication Routes
|--------------------------------------------------------------------------
|
| Every route below now authenticates through the single unified
| AuthController/`web` guard (Phase 1). URLs and route names are kept
| exactly as before so existing views/JS don't need to change — only the
| identity/permission layer underneath them changed.
|
*/

Route::get('/login', [AuthController::class, 'login'])->name('user.login');
Route::get('/', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'login_submit'])
    ->middleware('throttle:5,1')
    ->name('user.login_submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('user.logout');

// Available to any authenticated user regardless of module permissions.
Route::middleware('user_middle')->group(function () {
    Route::get('/dashboard', [DashController::class, 'index'])->name('user.dashboard');
    Route::get('/get-dashboard-data', [DashController::class, 'dashboard_data'])->name('user.dashboard_data');
    Route::get('/profile', [ProfileController::class, 'profile'])->name('user.profile');
    Route::post('/user-attendence', [LoginController::class, 'user_attend'])->name('user.user_attendence');
    Route::get('/get-attendence', [DashController::class, 'getAttendence'])->name('user.getAttendence');

    Route::get('/security', [SecurityController::class, 'security'])->name('user.security');
    Route::post('/update-security-pass', [SecurityController::class, 'update_security_pass'])->name('user.update_security_pass');
});

Route::middleware(['user_middle', 'permission:leads.view'])->group(function () {
    Route::get('/get-lead-list', [LeadListController::class, 'get_lead_list'])->name('user.get_lead_list');

    Route::get('/lead-list', [LeadListController::class, 'leadList'])->name('user.lead_list');
    Route::get('/view-lead/{id}', [LeadListController::class, 'viewleadList'])->name('user.view_lead_list');
    Route::get('/closed-lead/{id}', [LeadListController::class, 'viewclosedlead'])->name('user.view_closed_lead');
    Route::get('/get-view-lead', [LeadListController::class, 'get_viewLead_data'])->name('user.get_viewLead_data');
    Route::get('/get-lead-followups', [LeadListController::class, 'get_leadFollowups'])->name('user.get_leadFollowups');
    Route::post('/user-lead-call-update', [LeadListController::class, 'user_lead_call_update'])->name('user.user_lead_call_update');
    Route::post('/order-update', [LeadListController::class, 'order_success'])->name('user.order_success');

    Route::get('/closed-lead-list', [LeadListController::class, 'closed_lead_list'])->name('user.closed_lead_list');
    Route::get('/get-closed-lead-list', [LeadListController::class, 'get_closed_lead_list'])->name('user.get_closed_lead_list');

    Route::post('/user-login', [LeadListController::class, 'update_lead_status'])->name('user.update_lead_status');
});

Route::middleware(['user_middle', 'permission:orders.view'])->group(function () {
    Route::get('/quotation-template-1/{id}', [QuotationController::class, 'quotation_template_1'])
        ->name('user.quotation.template1');
    Route::get('/quotation-template-2/{id}', [QuotationController::class, 'quotation_template_2'])
        ->name('user.quotation.template2');
    Route::get('/quotation-template-3/{id}', [QuotationController::class, 'quotation_template_3'])
        ->name('user.quotation.template3');

    Route::get('/order-management', [OrderPaymentController::class, 'order_management'])->name('user.order_management');
    Route::get('/get-order-payment', [OrderPaymentController::class, 'get_order_payments'])->name('user.get_order_payments');
    Route::get('/order/{id}', [OrderPaymentController::class, 'order_view'])->name('user.order_view');
    Route::get('/order-payment-data', [OrderPaymentController::class, 'get_payment_data'])->name('user.get_payment_data');
    Route::post('/add-order-payment', [OrderPaymentController::class, 'add_order_payment'])->name('user.add_order_payment');

    route::get('/order-invoice/{id}', [InvoiceController::class, 'invoice'])->name('user.invoice');
});

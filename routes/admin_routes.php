<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\admin\CompanyController;
use App\Http\Controllers\admin\CustomerContactController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\OrderController;
use App\Http\Controllers\admin\ProjectDetailsController;
use App\Http\Controllers\admin\SecurityController;
use App\Http\Controllers\admin\TrackLeadController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\MasterValueLookupController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Every route below now authenticates through the single unified
| AuthController/`web` guard (Phase 1). URLs and route names are kept
| exactly as before so existing views/JS don't need to change — only the
| identity/permission layer underneath them changed.
|
*/

Route::get('/login', [AuthController::class, 'login'])->name('admin.login');
Route::get('/', [AuthController::class, 'login']);
Route::post('/login-submit', [AuthController::class, 'login_submit'])
    ->middleware('throttle:5,1')
    ->name('admin.login_submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Available to any authenticated user regardless of module permissions.
// Dashboard now lives at the unified /dashboard route (routes/app.php).
Route::middleware('admin_middle')->group(function () {
    Route::get('/security', [SecurityController::class, 'security'])->name('admin.security');
    Route::post('/update-password', [SecurityController::class, 'update_pass'])->name('admin.update_pass');

    // Read-only dropdown data — any authenticated role can look these up
    // (needed to fill out forms); managing the values themselves is
    // permission-gated below.
    Route::get('/master-data/lookup/{type}', [MasterValueLookupController::class, 'options'])->name('admin.master_data.lookup');
});

Route::middleware(['admin_middle', 'permission:masters.view'])->group(function () {
    Route::get('/master-data', [MasterDataController::class, 'index'])->name('admin.master_data.index');
    Route::get('/master-data/types', [MasterDataController::class, 'getTypes'])->name('admin.master_data.types');
    Route::get('/master-data/values', [MasterDataController::class, 'getValues'])->name('admin.master_data.values');
});

Route::middleware(['admin_middle', 'permission:masters.create'])->group(function () {
    Route::post('/master-data/values', [MasterDataController::class, 'store'])->name('admin.master_data.values.store');
});

Route::middleware(['admin_middle', 'permission:masters.edit'])->group(function () {
    Route::put('/master-data/values/{id}', [MasterDataController::class, 'update'])->name('admin.master_data.values.update');
    Route::post('/master-data/values/{id}/toggle-status', [MasterDataController::class, 'toggleStatus'])->name('admin.master_data.values.toggle');
});

Route::middleware(['admin_middle', 'permission:masters.delete'])->group(function () {
    Route::delete('/master-data/values/{id}', [MasterDataController::class, 'destroy'])->name('admin.master_data.values.destroy');
});

Route::middleware(['admin_middle', 'permission:users.view'])->group(function () {
    route::get('/user-profile', [UserController::class, 'user_profile'])->name('admin.user_profile');
    Route::get('/get-user-list', [UserController::class, 'getUserList'])->name('admin.get_user_list');
    Route::get('/get-roles', [UserController::class, 'getRoles'])->name('admin.get_roles');
    Route::post('/add-user-list', [UserController::class, 'add_user'])->name('admin.add_user');
    Route::post('/user/toggle-status', [UserController::class, 'toggleUserStatus'])
        ->name('admin.toggle_user_status');
    Route::post('/edit-user', [UserController::class, 'edit_user'])->name('admin.edit_user');
    Route::post('/delete-user', [UserController::class, 'delete_user'])->name('admin.delete_user');
});

Route::middleware(['admin_middle', 'permission:users.impersonate'])->group(function () {
    Route::post('/user-login', [AuthController::class, 'impersonate'])->name('admin.user_login_through_admin');
});

// Leads moved to the unified /leads routes (routes/app.php). Track Lead
// stays here — not part of this increment's merge.
Route::middleware(['admin_middle', 'permission:leads.view'])->group(function () {
    Route::get('/track-lead', [TrackLeadController::class, 'track_lead'])->name('admin.track_lead');
    Route::get('/get-track-lead', [TrackLeadController::class, 'get_track_leads'])->name('admin.get_track_leads');
    Route::get('/view-track-lead/{lead_id}', [TrackLeadController::class, 'view_track_leads'])->name('admin.view_track_leads');
    Route::get('/get-followups-details', [TrackLeadController::class, 'get_followups_detials'])->name('admin.get_followups_detials');
});

Route::middleware(['admin_middle', 'permission:orders.view'])->group(function () {
    ROUTE::get('/sales-orders', [OrderController::class, 'sales_orders'])->name('admin.sales_orders');
    ROUTE::get('/edit-sales-orders', [OrderController::class, 'edit_sales_orders'])->name('admin.edit_sales_orders');
    ROUTE::get('/get-order-list', [OrderController::class, 'get_order_list'])->name('admin.get_order_list');
    ROUTE::get('/view-sales-order-list', [OrderController::class, 'view_sales_order_list'])->name('admin.view_sales_order_list');
    ROUTE::get('/get-view-sales-order-list', [OrderController::class, 'get_view_sales_order_list'])->name('admin.get_view_sales_order_list');
    ROUTE::get('/get-edit-data-list', [OrderController::class, 'get_order_by_id'])->name('admin.get_order_by_id');
    ROUTE::post('/update-sales-list', [OrderController::class, 'update_sales_order'])->name('admin.update_sales_order');

    ROUTE::get('/project-details', [ProjectDetailsController::class, 'project_details'])->name('admin.project_details');
    ROUTE::get('/get-project-details', [ProjectDetailsController::class, 'get_project_details'])->name('admin.get_project_details');
    ROUTE::post('/update-project-details', [ProjectDetailsController::class, 'update_project_details'])->name('admin.update_project_details');
});

Route::middleware(['admin_middle', 'permission:company.view'])->group(function () {
    Route::get('/company-details', [CompanyController::class, 'company_details'])->name('admin.company_details');
    Route::get('/edit-company-details_page', [CompanyController::class, 'edit_company_details_page'])->name('admin.edit_company_details_page');
    Route::post('/edit-comany-details', [CompanyController::class, 'edit_com_details'])->name('admin.edit_com_details');
});

Route::middleware(['admin_middle', 'permission:contacts.view'])->group(function () {
    Route::get('/customer-contact-details', [CustomerContactController::class, 'customer_contact_details'])->name('admin.customer_contact');
    Route::get('/get-customer-contact-details', [CustomerContactController::class, 'get_customer_contacts'])->name('admin.get_customer_contacts');
});

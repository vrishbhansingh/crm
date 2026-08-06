<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\admin\CompanyController;
use App\Http\Controllers\admin\CustomerContactController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\admin\OrderController;
use App\Http\Controllers\admin\ProjectDetailsController;
use App\Http\Controllers\admin\SecurityController;
use App\Http\Controllers\admin\TrackLeadController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\LeadController;

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
Route::middleware('admin_middle')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/get-dashboard-data', [DashboardController::class, 'getDashboardData'])->name('admin.getDashboardData');
    Route::get('/get-attendance', [DashboardController::class, 'getAttendance'])->name('admin.getAttendance');

    Route::get('/security', [SecurityController::class, 'security'])->name('admin.security');
    Route::post('/update-password', [SecurityController::class, 'update_pass'])->name('admin.update_pass');
});

Route::middleware(['admin_middle', 'permission:users.view'])->group(function () {
    route::get('/user-profile', [UserController::class, 'user_profile'])->name('admin.user_profile');
    Route::get('/get-user-list', [UserController::class, 'getUserList'])->name('admin.get_user_list');
    Route::post('/add-user-list', [UserController::class, 'add_user'])->name('admin.add_user');
    Route::post('/user/toggle-status', [UserController::class, 'toggleUserStatus'])
        ->name('admin.toggle_user_status');
    Route::post('/edit-user', [UserController::class, 'edit_user'])->name('admin.edit_user');
    Route::post('/delete-user', [UserController::class, 'delete_user'])->name('admin.delete_user');
});

Route::middleware(['admin_middle', 'permission:users.impersonate'])->group(function () {
    Route::post('/user-login', [AuthController::class, 'impersonate'])->name('admin.user_login_through_admin');
});

Route::middleware(['admin_middle', 'permission:leads.view'])->group(function () {
    Route::get('/leads', [LeadController::class, 'lead'])->name('admin.lead');
    Route::get('/getLeads', [LeadController::class, 'get_lead'])->name('admin.get_lead');
    Route::get('/getAssignUsers', [LeadController::class, 'getAssignUsers'])->name('admin.getAssignUsers');
    Route::post('/lead/toggle-status', [LeadController::class, 'toggleLeadStatus'])->name('admin.toggle_lead_status');
    Route::post('/update-lead-status', [LeadController::class, 'updateLead'])->name('admin.update_lead_status');
    Route::post('/update-assignLead', [LeadController::class, 'assignLead'])->name('admin.assignLead');
    Route::post('/update-bulk-assignLead', [LeadController::class, 'bulkAssignLead'])->name('admin.bulkAssignLeads');

    Route::get('/addLeadsPage', [LeadController::class, 'add_lead_view'])->name('admin.add_lead_view');
    Route::post('/add-lead', [LeadController::class, 'add_lead'])->name('admin.add_lead');
    Route::get('/editLeadsPage/{id}', [LeadController::class, 'edit_lead_view'])->name('admin.edit_lead_view');
    route::post('/edit-lead-data', [LeadController::class, 'edit_lead_data'])->name('admin.edit_lead_data');
    route::post('/leads-import', [LeadController::class, 'leads_import'])->name('admin.leads_import');

    route::get('/get-edit-lead-data', [LeadController::class, 'get_edit_lead_data'])->name('admin.get_edit_lead_data');
    route::post('/delete-lead-data', [LeadController::class, 'delete_lead_data'])->name('admin.delete_lead_data');

    Route::get('/download/leads-format', [LeadController::class, 'downloadFormat'])->name('admin.leads.format.download');

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

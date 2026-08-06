<?php

use App\Http\Controllers\Admin\LeadDetailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Unified interface routes
|--------------------------------------------------------------------------
|
| No /admin or /user prefix — one URL per page, reachable by any role with
| the right permission. Content and data scope adapt via
| Auth::user()->hasElevatedAccess() inside each controller, not by which
| namespace the route lives in. Guarded by the same single-session
| middleware ('admin_middle', aliased to EnsureSingleSession — see
| app/Http/Kernel.php) used everywhere else.
|
*/

Route::middleware('admin_middle')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
    Route::get('/dashboard/attendance/team', [DashboardController::class, 'attendanceTeam'])->name('dashboard.attendance.team');
    Route::get('/dashboard/attendance/status', [DashboardController::class, 'attendanceStatus'])->name('dashboard.attendance.status');
    Route::post('/dashboard/attendance/check-in', [DashboardController::class, 'checkIn'])->name('dashboard.attendance.checkin');
});

/*
|--------------------------------------------------------------------------
| Unified Leads (increment 1) — replaces Admin\LeadController's /admin/leads*
| and the fully-absorbed User\LeadListController's /user/lead-list* routes.
| Data scope (all tenant leads vs. only-my-leads) is decided inside the
| controllers via hasElevatedAccess(), not by which of these routes you hit.
|--------------------------------------------------------------------------
*/

Route::middleware(['admin_middle', 'permission:leads.view'])->group(function () {
    Route::get('/leads', [LeadController::class, 'lead'])->name('leads.index');
    Route::get('/leads/data', [LeadController::class, 'get_lead'])->name('leads.data');
    Route::get('/leads/assignable-users', [LeadController::class, 'getAssignUsers'])->name('leads.assignable_users');
    Route::get('/leads/edit-data', [LeadController::class, 'get_edit_lead_data'])->name('leads.edit_data');
    Route::get('/leads/download-format', [LeadController::class, 'downloadFormat'])->name('leads.download_format');

    Route::post('/leads/toggle-status', [LeadController::class, 'toggleLeadStatus'])->name('leads.toggle_status');
    Route::post('/leads/update-status', [LeadController::class, 'updateLead'])->name('leads.update_status');

    Route::post('/leads/check-duplicate', [LeadDetailController::class, 'checkDuplicate'])->name('leads.check_duplicate');
    Route::get('/leads/{id}/detail', [LeadDetailController::class, 'detail'])->name('leads.detail');
    Route::get('/leads/{id}/timeline', [LeadDetailController::class, 'timeline'])->name('leads.timeline');
    Route::post('/leads/{id}/notes', [LeadDetailController::class, 'addNote'])->name('leads.notes.store');
    Route::post('/leads/{id}/tags', [LeadDetailController::class, 'addTag'])->name('leads.tags.store');
    Route::delete('/leads/{id}/tags/{tagId}', [LeadDetailController::class, 'removeTag'])->name('leads.tags.destroy');
    Route::post('/leads/{id}/attachments', [LeadDetailController::class, 'uploadAttachment'])->name('leads.attachments.store');
    Route::get('/attachments/{attachmentId}/download', [LeadDetailController::class, 'downloadAttachment'])->name('leads.attachments.download');
    Route::post('/leads/{id}/follow-up', [LeadDetailController::class, 'storeFollowUp'])->name('leads.follow_up.store');
});

Route::middleware(['admin_middle', 'permission:leads.create'])->group(function () {
    Route::get('/leads/create', [LeadController::class, 'add_lead_view'])->name('leads.create');
    Route::post('/leads', [LeadController::class, 'add_lead'])->name('leads.store');
    Route::post('/leads/import', [LeadController::class, 'leads_import'])->name('leads.import');
});

Route::middleware(['admin_middle', 'permission:leads.edit'])->group(function () {
    Route::get('/leads/{id}/edit', [LeadController::class, 'edit_lead_view'])->name('leads.edit');
    Route::post('/leads/update', [LeadController::class, 'edit_lead_data'])->name('leads.update');
});

Route::middleware(['admin_middle', 'permission:leads.delete'])->group(function () {
    Route::post('/leads/delete', [LeadController::class, 'delete_lead_data'])->name('leads.delete');
});

Route::middleware(['admin_middle', 'permission:leads.assign'])->group(function () {
    Route::post('/leads/assign', [LeadController::class, 'assignLead'])->name('leads.assign');
    Route::post('/leads/bulk-assign', [LeadController::class, 'bulkAssignLead'])->name('leads.bulk_assign');
});

Route::middleware(['admin_middle', 'permission:orders.create'])->group(function () {
    Route::post('/leads/{id}/convert-to-order', [LeadDetailController::class, 'convertToOrder'])->name('leads.convert_to_order');
});

// Bare /leads/{id} last, constrained to digits so it can never shadow the
// literal routes above (create, data, assignable-users, ...).
Route::middleware(['admin_middle', 'permission:leads.view'])->group(function () {
    Route::get('/leads/{id}', [LeadDetailController::class, 'show'])->name('leads.show')->where('id', '[0-9]+');
});

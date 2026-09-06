<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\admin\CompanyController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\CrmCompanyController;
use App\Http\Controllers\Admin\DealController;
use App\Http\Controllers\Admin\DealDetailController;
use App\Http\Controllers\Admin\EmailCampaignController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\LeadDetailController;
use App\Http\Controllers\Admin\MailSettingsController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OrderDetailController;
use App\Http\Controllers\Admin\PipelineController;
use App\Http\Controllers\Admin\PipelineStageController;
use App\Http\Controllers\Admin\ProjectDetailsController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\MasterValueLookupController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\User\InvoiceController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\QuotationController;
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
});

Route::middleware(['admin_middle', 'permission:tasks.view'])->group(function () {
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/data', [TaskController::class, 'data'])->name('tasks.data');
    Route::get('/tasks/related-options/{type}', [TaskController::class, 'relatedOptions'])->name('tasks.related_options');
});

Route::middleware(['admin_middle', 'permission:tasks.create'])->group(function () {
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/assignable-users', [TaskController::class, 'assignableUsers'])->name('tasks.assignable_users');
});

Route::middleware(['admin_middle', 'permission:tasks.edit'])->group(function () {
    Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('tasks.update')->whereNumber('id');
    Route::post('/tasks/{id}/complete', [TaskController::class, 'complete'])->name('tasks.complete')->whereNumber('id');
});

Route::middleware(['admin_middle', 'permission:tasks.delete'])->group(function () {
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy')->whereNumber('id');
});

Route::middleware(['admin_middle', 'permission:calendar.view'])->group(function () {
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
});

Route::middleware(['admin_middle', 'permission:audit.view'])->group(function () {
    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit.index');
    Route::get('/audit-log/data', [AuditLogController::class, 'data'])->name('audit.data');
});

Route::middleware(['admin_middle', 'permission:reports.view'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/data', [ReportController::class, 'data'])->name('reports.data');
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
    Route::get('/leads/{id}/detail', [LeadDetailController::class, 'detail'])->name('leads.detail');
    Route::get('/leads/{id}/timeline', [LeadDetailController::class, 'timeline'])->name('leads.timeline');
    Route::get('/attachments/{attachmentId}/download', [LeadDetailController::class, 'downloadAttachment'])->name('leads.attachments.download');
});

Route::middleware(['admin_middle', 'permission:leads.create'])->group(function () {
    Route::get('/leads/create', [LeadController::class, 'add_lead_view'])->name('leads.create');
    Route::post('/leads', [LeadController::class, 'add_lead'])->name('leads.store');
    Route::post('/leads/check-duplicate', [LeadDetailController::class, 'checkDuplicate'])->name('leads.check_duplicate');
    Route::get('/leads/download-format', [LeadController::class, 'downloadFormat'])->name('leads.download_format');
});

Route::middleware(['admin_middle', 'permission:leads.import'])->group(function () {
    Route::post('/leads/import', [LeadController::class, 'leads_import'])->name('leads.import');
});

Route::middleware(['admin_middle', 'permission:leads.edit'])->group(function () {
    Route::get('/leads/{id}/edit', [LeadController::class, 'edit_lead_view'])->name('leads.edit');
    Route::get('/leads/edit-data', [LeadController::class, 'get_edit_lead_data'])->name('leads.edit_data');
    Route::post('/leads/update', [LeadController::class, 'edit_lead_data'])->name('leads.update');
    Route::post('/leads/toggle-status', [LeadController::class, 'toggleLeadStatus'])->name('leads.toggle_status');
    Route::post('/leads/update-status', [LeadController::class, 'updateLead'])->name('leads.update_status');
    Route::post('/leads/{id}/notes', [LeadDetailController::class, 'addNote'])->name('leads.notes.store');
    Route::post('/leads/{id}/tags', [LeadDetailController::class, 'addTag'])->name('leads.tags.store');
    Route::delete('/leads/{id}/tags/{tagId}', [LeadDetailController::class, 'removeTag'])->name('leads.tags.destroy');
    Route::post('/leads/{id}/attachments', [LeadDetailController::class, 'uploadAttachment'])->name('leads.attachments.store');
    Route::post('/leads/{id}/follow-up', [LeadDetailController::class, 'storeFollowUp'])->name('leads.follow_up.store');
});

Route::middleware(['admin_middle', 'permission:leads.delete'])->group(function () {
    Route::post('/leads/delete', [LeadController::class, 'delete_lead_data'])->name('leads.delete');
});

Route::middleware(['admin_middle', 'permission:leads.assign'])->group(function () {
    Route::post('/leads/assign', [LeadController::class, 'assignLead'])->name('leads.assign');
    Route::post('/leads/bulk-assign', [LeadController::class, 'bulkAssignLead'])->name('leads.bulk_assign');
});

Route::middleware(['admin_middle', 'permission:deals.create'])->group(function () {
    Route::post('/leads/{id}/convert-to-deal', [LeadDetailController::class, 'convertToDeal'])->name('leads.convert_to_deal');
});

// Bare /leads/{id} last, constrained to digits so it can never shadow the
// literal routes above (create, data, assignable-users, ...).
Route::middleware(['admin_middle', 'permission:leads.view'])->group(function () {
    Route::get('/leads/{id}', [LeadDetailController::class, 'show'])->name('leads.show')->where('id', '[0-9]+');
});

/*
|--------------------------------------------------------------------------
| Deals & Pipelines (Phase 5) — Lead -> Deal -> Order. Converting a lead now
| creates a Deal (see leads.convert_to_deal above); an Order is only created
| once a deal reaches a Won stage (DealController::moveStage). Data scope
| (all tenant deals vs only-my-deals) is decided inside the controller via
| hasElevatedAccess(), same as Leads/Orders.
|--------------------------------------------------------------------------
*/

Route::middleware(['admin_middle', 'permission:deals.view'])->group(function () {
    Route::get('/deals', [DealController::class, 'index'])->name('deals.index');
    Route::get('/deals/list', [DealController::class, 'listView'])->name('deals.list');
    Route::get('/deals/data', [DealController::class, 'data'])->name('deals.data');
    Route::get('/deals/board-data', [DealController::class, 'boardData'])->name('deals.board_data');
    Route::get('/deals/pipeline-options', [DealController::class, 'pipelineOptions'])->name('deals.pipeline_options');
});

Route::middleware(['admin_middle', 'permission:deals.create'])->group(function () {
    Route::get('/deals/create', [DealController::class, 'create'])->name('deals.create');
    Route::post('/deals', [DealController::class, 'store'])->name('deals.store');
});

Route::middleware(['admin_middle', 'permission:deals.edit'])->group(function () {
    Route::get('/deals/{id}/edit', [DealController::class, 'edit'])->name('deals.edit')->where('id', '[0-9]+');
    Route::post('/deals/{id}', [DealController::class, 'update'])->name('deals.update')->where('id', '[0-9]+');
    Route::post('/deals/{id}/move-stage', [DealController::class, 'moveStage'])->name('deals.move_stage')->where('id', '[0-9]+');
});

Route::middleware(['admin_middle', 'permission:deals.delete'])->group(function () {
    Route::post('/deals/{id}/delete', [DealController::class, 'destroy'])->name('deals.destroy')->where('id', '[0-9]+');
});

Route::middleware(['admin_middle', 'permission:deals.assign'])->group(function () {
    Route::post('/deals/assign', [DealController::class, 'assign'])->name('deals.assign');
});

Route::middleware(['admin_middle', 'permission:deals.manage-settings'])->group(function () {
    Route::get('/pipelines', [PipelineController::class, 'index'])->name('pipelines.index');
    Route::get('/pipelines/data', [PipelineController::class, 'data'])->name('pipelines.data');
    Route::post('/pipelines', [PipelineController::class, 'store'])->name('pipelines.store');
    Route::put('/pipelines/{id}', [PipelineController::class, 'update'])->name('pipelines.update');
    Route::post('/pipelines/{id}/toggle-status', [PipelineController::class, 'toggleStatus'])->name('pipelines.toggle');
    Route::delete('/pipelines/{id}', [PipelineController::class, 'destroy'])->name('pipelines.destroy');

    Route::get('/pipelines/{id}/stages', [PipelineStageController::class, 'data'])->name('stages.data');
    Route::post('/pipelines/{id}/stages', [PipelineStageController::class, 'store'])->name('stages.store');
    Route::put('/stages/{id}', [PipelineStageController::class, 'update'])->name('stages.update');
    Route::delete('/stages/{id}', [PipelineStageController::class, 'destroy'])->name('stages.destroy');
});

// Bare /deals/{id} last, constrained to digits so it can never shadow the
// literal routes above (create, list, data, board-data, ...).
Route::middleware(['admin_middle', 'permission:deals.view'])->group(function () {
    Route::get('/deals/{id}', [DealDetailController::class, 'show'])->name('deals.show')->where('id', '[0-9]+');
    Route::get('/deals/{id}/detail', [DealDetailController::class, 'detail'])->name('deals.detail')->where('id', '[0-9]+');
    Route::get('/deals/{id}/timeline', [DealDetailController::class, 'timeline'])->name('deals.timeline')->where('id', '[0-9]+');
});

/*
|--------------------------------------------------------------------------
| Unified interface, increment 2 — everything else that was still split
| across /admin/... and /user/.... Security/Profile have no admin/user
| behavioral split (Security's old user-side version even skipped the
| current-password check — fixed by merging into one controller everyone
| goes through); Company Details/Master Data/Contacts/Users never had a
| user-side version at all, just an /admin/ prefix to drop.
|--------------------------------------------------------------------------
*/

Route::middleware('admin_middle')->group(function () {
    Route::get('/security', [SecurityController::class, 'show'])->name('security.show');
    Route::post('/security', [SecurityController::class, 'update'])->name('security.update');
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/api-tokens', [ApiTokenController::class, 'index'])->name('api_tokens.index');
    Route::post('/api-tokens', [ApiTokenController::class, 'store'])->name('api_tokens.store');
    Route::delete('/api-tokens/{id}', [ApiTokenController::class, 'destroy'])->name('api_tokens.destroy')->whereNumber('id');

    Route::get('/master-data/lookup/{type}', [MasterValueLookupController::class, 'options'])->name('master_data.lookup');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/data', [NotificationController::class, 'data'])->name('notifications.data');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read_all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
});

Route::middleware(['admin_middle', 'permission:company.view'])->group(function () {
    Route::get('/company-details', [CompanyController::class, 'company_details'])->name('company.show');
});

Route::middleware(['admin_middle', 'permission:company.edit'])->group(function () {
    Route::get('/company-details/edit', [CompanyController::class, 'edit_company_details_page'])->name('company.edit');
    Route::post('/company-details', [CompanyController::class, 'edit_com_details'])->name('company.update');
});

Route::middleware(['admin_middle', 'permission:company.manage-settings'])->group(function () {
    Route::get('/settings/mail', [MailSettingsController::class, 'edit'])->name('settings.mail.edit');
    Route::post('/settings/mail', [MailSettingsController::class, 'store'])->name('settings.mail.store');
    Route::put('/settings/mail/{mailSetting}', [MailSettingsController::class, 'update'])->name('settings.mail.update')->whereNumber('mailSetting');
    Route::post('/settings/mail/{mailSetting}/activate', [MailSettingsController::class, 'activate'])->name('settings.mail.activate')->whereNumber('mailSetting');
    Route::delete('/settings/mail/{mailSetting}', [MailSettingsController::class, 'destroy'])->name('settings.mail.destroy')->whereNumber('mailSetting');
    Route::post('/settings/mail/test', [MailSettingsController::class, 'test'])->name('settings.mail.test');
});

Route::middleware(['admin_middle', 'permission:templates.view'])->group(function () {
    Route::get('/email-templates', [EmailTemplateController::class, 'index'])->name('templates.index');
    Route::get('/email-templates/data', [EmailTemplateController::class, 'data'])->name('templates.data');
    Route::get('/email-templates/variables', [EmailTemplateController::class, 'variables'])->name('templates.variables');
    Route::post('/email-templates/preview', [EmailTemplateController::class, 'preview'])->name('templates.preview');
});

Route::middleware(['admin_middle', 'permission:templates.create'])->group(function () {
    Route::get('/email-templates/create', [EmailTemplateController::class, 'create'])->name('templates.create');
    Route::post('/email-templates', [EmailTemplateController::class, 'store'])->name('templates.store');
});

Route::middleware(['admin_middle', 'permission:templates.edit'])->group(function () {
    Route::get('/email-templates/{id}/edit', [EmailTemplateController::class, 'edit'])->name('templates.edit')->whereNumber('id');
    Route::put('/email-templates/{emailTemplate}', [EmailTemplateController::class, 'update'])->name('templates.update');
});

Route::middleware(['admin_middle', 'permission:templates.delete'])->group(function () {
    Route::delete('/email-templates/{emailTemplate}', [EmailTemplateController::class, 'destroy'])->name('templates.destroy');
});

Route::middleware(['admin_middle', 'permission:campaigns.view'])->group(function () {
    Route::get('/email-campaigns', [EmailCampaignController::class, 'index'])->name('campaigns.index');
    Route::get('/email-campaigns/data', [EmailCampaignController::class, 'data'])->name('campaigns.data');
    Route::get('/email-campaigns/{emailCampaign}', [EmailCampaignController::class, 'show'])->name('campaigns.show');
    Route::post('/email-campaigns/preview-audience', [EmailCampaignController::class, 'previewAudience'])->name('campaigns.preview_audience');
});

Route::middleware(['admin_middle', 'permission:campaigns.create'])->group(function () {
    Route::post('/email-campaigns', [EmailCampaignController::class, 'store'])->name('campaigns.store');
});

Route::middleware(['admin_middle', 'permission:campaigns.delete'])->group(function () {
    Route::delete('/email-campaigns/{emailCampaign}', [EmailCampaignController::class, 'destroy'])->name('campaigns.destroy');
});

Route::middleware(['admin_middle', 'permission:campaigns.send'])->group(function () {
    Route::post('/email-campaigns/{emailCampaign}/send', [EmailCampaignController::class, 'send'])->name('campaigns.send');
});

Route::middleware(['admin_middle', 'permission:masters.view'])->group(function () {
    Route::get('/master-data', [MasterDataController::class, 'index'])->name('master_data.index');
    Route::get('/master-data/types', [MasterDataController::class, 'getTypes'])->name('master_data.types');
    Route::get('/master-data/values', [MasterDataController::class, 'getValues'])->name('master_data.values');
});

Route::middleware(['admin_middle', 'permission:masters.create'])->group(function () {
    Route::post('/master-data/values', [MasterDataController::class, 'store'])->name('master_data.values.store');
});

Route::middleware(['admin_middle', 'permission:masters.edit'])->group(function () {
    Route::put('/master-data/values/{id}', [MasterDataController::class, 'update'])->name('master_data.values.update');
    Route::post('/master-data/values/{id}/toggle-status', [MasterDataController::class, 'toggleStatus'])->name('master_data.values.toggle');
});

Route::middleware(['admin_middle', 'permission:masters.delete'])->group(function () {
    Route::delete('/master-data/values/{id}', [MasterDataController::class, 'destroy'])->name('master_data.values.destroy');
});

Route::middleware(['admin_middle', 'permission:contacts.view'])->group(function () {
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/data', [ContactController::class, 'data'])->name('contacts.data');
    Route::get('/contacts/options', [ContactController::class, 'options'])->name('contacts.options');
});

Route::middleware(['admin_middle', 'permission:contacts.create'])->group(function () {
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
});

Route::middleware(['admin_middle', 'permission:contacts.edit'])->group(function () {
    Route::put('/contacts/{id}', [ContactController::class, 'update'])->name('contacts.update')->where('id', '[0-9]+');
});

Route::middleware(['admin_middle', 'permission:contacts.delete'])->group(function () {
    Route::delete('/contacts/{id}', [ContactController::class, 'destroy'])->name('contacts.destroy')->where('id', '[0-9]+');
});

Route::middleware(['admin_middle', 'permission:companies.view'])->group(function () {
    Route::get('/companies', [CrmCompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/data', [CrmCompanyController::class, 'data'])->name('companies.data');
    Route::get('/companies/options', [CrmCompanyController::class, 'options'])->name('companies.options');
    Route::get('/companies/{id}/detail', [CrmCompanyController::class, 'detail'])->name('companies.detail')->where('id', '[0-9]+');
    Route::get('/companies/{id}', [CrmCompanyController::class, 'show'])->name('companies.show')->where('id', '[0-9]+');
});

Route::middleware(['admin_middle', 'permission:companies.create'])->group(function () {
    Route::post('/companies', [CrmCompanyController::class, 'store'])->name('companies.store');
});

Route::middleware(['admin_middle', 'permission:companies.edit'])->group(function () {
    Route::put('/companies/{id}', [CrmCompanyController::class, 'update'])->name('companies.update')->where('id', '[0-9]+');
});

Route::middleware(['admin_middle', 'permission:companies.delete'])->group(function () {
    Route::delete('/companies/{id}', [CrmCompanyController::class, 'destroy'])->name('companies.destroy')->where('id', '[0-9]+');
});

Route::middleware(['admin_middle', 'permission:users.view'])->group(function () {
    Route::get('/users', [UserController::class, 'user_profile'])->name('users.index');
    Route::get('/users/data', [UserController::class, 'getUserList'])->name('users.data');
    Route::get('/users/roles', [UserController::class, 'getRoles'])->name('users.roles');
});

Route::middleware(['admin_middle', 'permission:users.create'])->group(function () {
    Route::post('/users', [UserController::class, 'add_user'])->name('users.store');
});

Route::middleware(['admin_middle', 'permission:users.edit'])->group(function () {
    Route::post('/users/toggle-status', [UserController::class, 'toggleUserStatus'])->name('users.toggle_status');
    Route::post('/users/update', [UserController::class, 'edit_user'])->name('users.update');
});

Route::middleware(['admin_middle', 'permission:users.delete'])->group(function () {
    Route::post('/users/delete', [UserController::class, 'delete_user'])->name('users.destroy');
});

Route::middleware(['admin_middle', 'permission:users.impersonate'])->group(function () {
    Route::post('/users/impersonate', [AuthController::class, 'impersonate'])->name('users.impersonate');
});

// Lives here (not under routes/superadmin.php) so the "End support session"
// banner shown while impersonating — rendered on the CRM domain — stays
// reachable under RestrictToAdminDomain's domain split. Its own guard is
// the live `impersonator_id` session value, not the URL namespace.
Route::middleware('admin_middle')->post('/impersonation/stop', [AuthController::class, 'stopImpersonating'])->name('impersonation.stop');

Route::middleware(['admin_middle', 'permission:roles.view'])->group(function () {
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/data', [RoleController::class, 'data'])->name('roles.data');
    Route::get('/roles/permissions', [RoleController::class, 'permissionCatalog'])->name('roles.permissions.catalog');
    Route::get('/roles/{role}/permissions', [RoleController::class, 'permissionCatalog'])->name('roles.permissions.show')->whereNumber('role');
});
Route::middleware(['admin_middle', 'permission:roles.create'])->post('/roles', [RoleController::class, 'store'])->name('roles.store');
Route::middleware(['admin_middle', 'permission:roles.edit'])->group(function () {
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update')->whereNumber('role');
    Route::put('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update')->whereNumber('role');
});
Route::middleware(['admin_middle', 'permission:roles.delete'])->delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->whereNumber('role');

/*
|--------------------------------------------------------------------------
| Unified Orders (increment 2, Group B) — replaces Admin\OrderController's
| /admin/sales-orders* + /admin/*-order-list* routes and
| User\OrderPaymentController's /user/order-management* + /user/order/*
| routes. Data scope (all tenant orders vs only-my-orders) is decided
| inside the controller via hasElevatedAccess(), same as Leads. The detail
| page merges the old edit-order (elevated/orders.edit only, gated by
| @can in the view) and payment_management (payment history + add-payment
| form, anyone with orders.view) pages into one.
|--------------------------------------------------------------------------
*/

Route::middleware(['admin_middle', 'permission:orders.view'])->group(function () {
    Route::get('/orders', [OrderController::class, 'sales_orders'])->name('orders.index');
    Route::get('/orders/data', [OrderController::class, 'get_order_list'])->name('orders.data');

    Route::get('/orders/{id}/payments', [OrderDetailController::class, 'paymentsData'])->name('orders.payments.data')->where('id', '[0-9]+');

    Route::get('/orders/{id}', [OrderDetailController::class, 'show'])->name('orders.show')->where('id', '[0-9]+');

    Route::get('/projects', [ProjectDetailsController::class, 'project_details'])->name('projects.index');
    Route::get('/projects/data', [ProjectDetailsController::class, 'get_project_details'])->name('projects.data');

    Route::get('/quotation/template-1/{id}', [QuotationController::class, 'quotation_template_1'])->name('quotation.template1');
    Route::get('/quotation/template-2/{id}', [QuotationController::class, 'quotation_template_2'])->name('quotation.template2');
    Route::get('/quotation/template-3/{id}', [QuotationController::class, 'quotation_template_3'])->name('quotation.template3');

    Route::get('/orders/{id}/invoice', [InvoiceController::class, 'invoice'])->name('invoice.show')->where('id', '[0-9]+');
});

Route::middleware(['admin_middle', 'permission:orders.edit'])->group(function () {
    Route::post('/orders/payments', [OrderDetailController::class, 'paymentsStore'])->name('orders.payments.store');
    Route::post('/orders/{id}', [OrderDetailController::class, 'update'])->name('orders.update')->where('id', '[0-9]+');
    Route::post('/projects/update', [ProjectDetailsController::class, 'update_project_details'])->name('projects.update');
});

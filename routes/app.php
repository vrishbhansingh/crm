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
use App\Http\Controllers\Admin\DealAssignmentController;
use App\Http\Controllers\Admin\LeadAssignmentController;
use App\Http\Controllers\Admin\LeadDetailController;
use App\Http\Controllers\Admin\MailSettingsController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OrderDetailController;
use App\Http\Controllers\Admin\PipelineController;
use App\Http\Controllers\Admin\PipelineStageController;
use App\Http\Controllers\Admin\GoodsReceiptController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\RfqController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\ProjectDetailsController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\TaxRateController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadIntegrationController;
use App\Http\Controllers\MasterValueLookupController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\User\InvoiceController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Admin\QuotationController as QuotationRecordController;
use App\Http\Controllers\Admin\WhatsAppAccountController;
use App\Http\Controllers\Admin\WhatsAppCampaignController;
use App\Http\Controllers\Admin\WhatsAppChatController;
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
    Route::get('/search', [SearchController::class, 'search'])->name('search');
});

Route::middleware(['admin_middle', 'permission:tasks.view'])->group(function () {
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/data', [TaskController::class, 'data'])->name('tasks.data');
    Route::get('/tasks/related-options/{type}', [TaskController::class, 'relatedOptions'])->name('tasks.related_options');
    Route::get('/tasks/workload', [TaskController::class, 'workload'])->name('tasks.workload');
    Route::get('/tasks/workload/data', [TaskController::class, 'workloadData'])->name('tasks.workload_data');
});

Route::middleware(['admin_middle', 'permission:tasks.create'])->group(function () {
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/assignable-users', [TaskController::class, 'assignableUsers'])->name('tasks.assignable_users');
    Route::post('/tasks/bulk', [TaskController::class, 'bulkCreate'])->name('tasks.bulk_create');
});

Route::middleware(['admin_middle', 'permission:tasks.edit'])->group(function () {
    Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('tasks.update')->whereNumber('id');
    Route::post('/tasks/{id}/complete', [TaskController::class, 'complete'])->name('tasks.complete')->whereNumber('id');
    Route::post('/tasks/{id}/checklist-toggle', [TaskController::class, 'checklistToggle'])->name('tasks.checklist_toggle')->whereNumber('id');
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
    Route::get('/reports/leads', [ReportController::class, 'leads'])->name('reports.leads');
    Route::get('/reports/follow-ups', [ReportController::class, 'followUps'])->name('reports.follow_ups');
    Route::get('/reports/agents', [ReportController::class, 'agents'])->name('reports.agents');
    Route::get('/reports/communications', [ReportController::class, 'communications'])->name('reports.communications');
    Route::get('/reports/automation', [ReportController::class, 'automation'])->name('reports.automation');
});

Route::middleware(['admin_middle', 'permission:leads.manage-settings'])->group(function () {
    Route::get('/settings/lead-assignment', [LeadAssignmentController::class, 'index'])->name('lead_assignment.index');
    Route::get('/settings/lead-assignment/data', [LeadAssignmentController::class, 'data'])->name('lead_assignment.data');
    Route::post('/settings/lead-assignment', [LeadAssignmentController::class, 'store'])->name('lead_assignment.store');
    Route::put('/settings/lead-assignment/{id}', [LeadAssignmentController::class, 'update'])->name('lead_assignment.update')->whereNumber('id');
    Route::delete('/settings/lead-assignment/{id}', [LeadAssignmentController::class, 'destroy'])->name('lead_assignment.destroy')->whereNumber('id');
});

Route::middleware(['admin_middle', 'permission:deals.manage-settings'])->group(function () {
    Route::get('/settings/deal-assignment', [DealAssignmentController::class, 'index'])->name('deal_assignment.index');
    Route::get('/settings/deal-assignment/data', [DealAssignmentController::class, 'data'])->name('deal_assignment.data');
    Route::post('/settings/deal-assignment', [DealAssignmentController::class, 'store'])->name('deal_assignment.store');
    Route::put('/settings/deal-assignment/{id}', [DealAssignmentController::class, 'update'])->name('deal_assignment.update')->whereNumber('id');
    Route::delete('/settings/deal-assignment/{id}', [DealAssignmentController::class, 'destroy'])->name('deal_assignment.destroy')->whereNumber('id');
});

Route::middleware(['admin_middle', 'permission:integrations.view'])->group(function () {
    Route::get('/integrations', [LeadIntegrationController::class, 'index'])->name('integrations.index');
    Route::get('/integrations/{integration}', [LeadIntegrationController::class, 'show'])->name('integrations.show')->whereNumber('integration');
    Route::get('/integrations/{integration}/logs', [LeadIntegrationController::class, 'logs'])->name('integrations.logs')->whereNumber('integration');
});
Route::middleware(['admin_middle', 'permission:integrations.create'])->group(function () {
    Route::post('/integrations', [LeadIntegrationController::class, 'store'])->name('integrations.store');
});
Route::middleware(['admin_middle', 'permission:integrations.edit'])->group(function () {
    Route::put('/integrations/{integration}', [LeadIntegrationController::class, 'update'])->name('integrations.update')->whereNumber('integration');
    Route::post('/integrations/{integration}/regenerate', [LeadIntegrationController::class, 'regenerate'])->name('integrations.regenerate')->whereNumber('integration');
});
Route::middleware(['admin_middle', 'permission:integrations.delete'])->group(function () {
    Route::delete('/integrations/{integration}', [LeadIntegrationController::class, 'destroy'])->name('integrations.destroy')->whereNumber('integration');
});

/*
|--------------------------------------------------------------------------
| WhatsApp — chat box, campaigns, and connected-account settings
|--------------------------------------------------------------------------
*/
Route::middleware(['admin_middle', 'permission:whatsapp.view'])->group(function () {
    Route::get('/whatsapp/chat', [WhatsAppChatController::class, 'index'])->name('whatsapp.chat');
    Route::get('/whatsapp/chat/conversations', [WhatsAppChatController::class, 'conversations'])->name('whatsapp.chat.conversations');
    Route::get('/whatsapp/chat/conversations/{id}/messages', [WhatsAppChatController::class, 'messages'])->name('whatsapp.chat.messages')->whereNumber('id');

    Route::get('/whatsapp/campaigns', [WhatsAppCampaignController::class, 'index'])->name('whatsapp.campaigns.index');
    Route::get('/whatsapp/campaigns/data', [WhatsAppCampaignController::class, 'data'])->name('whatsapp.campaigns.data');
    Route::post('/whatsapp/campaigns/preview-audience', [WhatsAppCampaignController::class, 'previewAudience'])->name('whatsapp.campaigns.preview_audience');
});
Route::middleware(['admin_middle', 'permission:whatsapp.send'])->group(function () {
    Route::post('/whatsapp/chat/conversations/{id}/send', [WhatsAppChatController::class, 'send'])->name('whatsapp.chat.send')->whereNumber('id');
    Route::post('/whatsapp/chat/start', [WhatsAppChatController::class, 'start'])->name('whatsapp.chat.start');
    Route::post('/whatsapp/campaigns/{id}/send', [WhatsAppCampaignController::class, 'send'])->name('whatsapp.campaigns.send')->whereNumber('id');
});
Route::middleware(['admin_middle', 'permission:whatsapp.create'])->group(function () {
    Route::post('/whatsapp/campaigns', [WhatsAppCampaignController::class, 'store'])->name('whatsapp.campaigns.store');
});
Route::middleware(['admin_middle', 'permission:whatsapp.delete'])->group(function () {
    Route::delete('/whatsapp/campaigns/{id}', [WhatsAppCampaignController::class, 'destroy'])->name('whatsapp.campaigns.destroy')->whereNumber('id');
});

Route::middleware(['admin_middle', 'permission:whatsapp.manage-settings'])->group(function () {
    Route::get('/whatsapp/settings', [WhatsAppAccountController::class, 'index'])->name('whatsapp.settings.index');
    Route::get('/whatsapp/settings/{account}', [WhatsAppAccountController::class, 'show'])->name('whatsapp.settings.show')->whereNumber('account');
    Route::get('/whatsapp/settings/{account}/logs', [WhatsAppAccountController::class, 'logs'])->name('whatsapp.settings.logs')->whereNumber('account');
    Route::post('/whatsapp/settings', [WhatsAppAccountController::class, 'store'])->name('whatsapp.settings.store');
    Route::put('/whatsapp/settings/{account}', [WhatsAppAccountController::class, 'update'])->name('whatsapp.settings.update')->whereNumber('account');
    Route::post('/whatsapp/settings/{account}/regenerate', [WhatsAppAccountController::class, 'regenerate'])->name('whatsapp.settings.regenerate')->whereNumber('account');
    Route::delete('/whatsapp/settings/{account}', [WhatsAppAccountController::class, 'destroy'])->name('whatsapp.settings.destroy')->whereNumber('account');
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
    Route::get('/leads/import-report/{filename}', [LeadController::class, 'downloadImportReport'])->name('leads.import_report.download');
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
    Route::post('/leads/{id}/follow-up/complete', [LeadDetailController::class, 'completeFollowUp'])->name('leads.follow_up.complete');
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
    Route::post('/email-templates/upload-image', [EmailTemplateController::class, 'uploadImage'])->name('templates.upload_image');
});

Route::middleware(['admin_middle', 'permission:templates.create'])->group(function () {
    Route::get('/email-templates/create', [EmailTemplateController::class, 'create'])->name('templates.create');
    Route::post('/email-templates', [EmailTemplateController::class, 'store'])->name('templates.store');
});

Route::middleware(['admin_middle', 'permission:templates.edit'])->group(function () {
    Route::get('/email-templates/{id}/edit', [EmailTemplateController::class, 'edit'])->name('templates.edit')->whereNumber('id');
    Route::put('/email-templates/{emailTemplate}', [EmailTemplateController::class, 'update'])->name('templates.update');
    Route::post('/email-templates/{id}/attachments', [EmailTemplateController::class, 'uploadAttachment'])->name('templates.attachments.store')->whereNumber('id');
    Route::delete('/email-templates/attachments/{attachmentId}', [EmailTemplateController::class, 'destroyAttachment'])->name('templates.attachments.destroy')->whereNumber('attachmentId');
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
    Route::get('/master-data/tax-rates', [TaxRateController::class, 'data'])->name('master_data.tax_rates.data');
});

Route::middleware(['admin_middle', 'permission:masters.create'])->group(function () {
    Route::post('/master-data/values', [MasterDataController::class, 'store'])->name('master_data.values.store');
    Route::post('/master-data/tax-rates', [TaxRateController::class, 'store'])->name('master_data.tax_rates.store');
});

Route::middleware(['admin_middle', 'permission:masters.edit'])->group(function () {
    Route::put('/master-data/values/{id}', [MasterDataController::class, 'update'])->name('master_data.values.update');
    Route::post('/master-data/values/{id}/toggle-status', [MasterDataController::class, 'toggleStatus'])->name('master_data.values.toggle');
    Route::put('/master-data/tax-rates/{id}', [TaxRateController::class, 'update'])->name('master_data.tax_rates.update');
    Route::post('/master-data/tax-rates/{id}/toggle-status', [TaxRateController::class, 'toggleStatus'])->name('master_data.tax_rates.toggle');
});

Route::middleware(['admin_middle', 'permission:masters.delete'])->group(function () {
    Route::delete('/master-data/values/{id}', [MasterDataController::class, 'destroy'])->name('master_data.values.destroy');
    Route::delete('/master-data/tax-rates/{id}', [TaxRateController::class, 'destroy'])->name('master_data.tax_rates.destroy');
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

Route::middleware(['admin_middle', 'permission:products.view'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/data', [ProductController::class, 'data'])->name('products.data');
    Route::get('/products/options', [ProductController::class, 'options'])->name('products.options');
});

Route::middleware(['admin_middle', 'permission:products.create'])->group(function () {
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::post('/products/categories', [ProductController::class, 'storeCategory'])->name('products.categories.store');
    Route::post('/products/uoms', [ProductController::class, 'storeUom'])->name('products.uoms.store');
    Route::post('/products/tax-rates', [ProductController::class, 'storeTaxRate'])->name('products.tax_rates.store');
});

Route::middleware(['admin_middle', 'permission:products.edit'])->group(function () {
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update')->whereNumber('id');
});

Route::middleware(['admin_middle', 'permission:products.delete'])->group(function () {
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy')->whereNumber('id');
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

    Route::get('/orders/{id}/invoice', [InvoiceController::class, 'invoice'])->name('invoice.show')->where('id', '[0-9]+');
});

/*
|--------------------------------------------------------------------------
| Quotations — a real, versioned Quotation entity, quotable from either a
| Lead or a Deal. Route names deliberately plural ('quotations.*') and the
| controller namespaced under Admin — replaces the old singular
| 'quotation.*' routes (3 static, unpersisted print templates driven off a
| Lead + Order) formerly registered here.
|--------------------------------------------------------------------------
*/

Route::middleware(['admin_middle', 'permission:quotations.view'])->group(function () {
    Route::get('/quotations', [QuotationRecordController::class, 'index'])->name('quotations.index');
    Route::get('/quotations/data', [QuotationRecordController::class, 'data'])->name('quotations.data');
    Route::get('/quotations/create', [QuotationRecordController::class, 'create'])->name('quotations.create_form');
    Route::get('/quotations/link-options/{type}', [QuotationRecordController::class, 'linkOptions'])->name('quotations.link_options');
    Route::get('/quotations/{id}/detail', [QuotationRecordController::class, 'detail'])->name('quotations.detail')->whereNumber('id');
    Route::get('/quotations/{id}/pdf', [QuotationRecordController::class, 'pdf'])->name('quotations.pdf')->whereNumber('id');
    Route::get('/quotations/{id}', [QuotationRecordController::class, 'show'])->name('quotations.show')->whereNumber('id');
});

Route::middleware(['admin_middle', 'permission:quotations.create'])->group(function () {
    Route::post('/quotations', [QuotationRecordController::class, 'store'])->name('quotations.store');
});

Route::middleware(['admin_middle', 'permission:quotations.edit'])->group(function () {
    Route::put('/quotations/{id}', [QuotationRecordController::class, 'update'])->name('quotations.update')->whereNumber('id');
    Route::post('/quotations/{id}/items', [QuotationRecordController::class, 'addItem'])->name('quotations.items.store')->whereNumber('id');
    Route::put('/quotations/{id}/items/{itemId}', [QuotationRecordController::class, 'updateItem'])->name('quotations.items.update')->whereNumber('id')->whereNumber('itemId');
    Route::delete('/quotations/{id}/items/{itemId}', [QuotationRecordController::class, 'removeItem'])->name('quotations.items.destroy')->whereNumber('id')->whereNumber('itemId');
});

Route::middleware(['admin_middle', 'permission:quotations.delete'])->group(function () {
    Route::delete('/quotations/{id}', [QuotationRecordController::class, 'destroy'])->name('quotations.destroy')->whereNumber('id');
});

/*
|--------------------------------------------------------------------------
| Vendors — simple tenant-scoped master for the purchase-order module.
|--------------------------------------------------------------------------
*/

Route::middleware(['admin_middle', 'permission:vendors.view'])->group(function () {
    Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index');
    Route::get('/vendors/data', [VendorController::class, 'data'])->name('vendors.data');
    Route::get('/vendors/options', [VendorController::class, 'options'])->name('vendors.options');
});

Route::middleware(['admin_middle', 'permission:vendors.create'])->group(function () {
    Route::post('/vendors', [VendorController::class, 'store'])->name('vendors.store');
});

Route::middleware(['admin_middle', 'permission:vendors.edit'])->group(function () {
    Route::put('/vendors/{id}', [VendorController::class, 'update'])->name('vendors.update')->whereNumber('id');
});

Route::middleware(['admin_middle', 'permission:vendors.delete'])->group(function () {
    Route::delete('/vendors/{id}', [VendorController::class, 'destroy'])->name('vendors.destroy')->whereNumber('id');
});

/*
|--------------------------------------------------------------------------
| Purchase Orders, RFQs and Goods Receipts — all share the purchase_orders.*
| permission module (RFQ/GRN are sub-workflows of procurement, not
| independent modules, to avoid permission sprawl).
|--------------------------------------------------------------------------
*/

Route::middleware(['admin_middle', 'permission:purchase_orders.view'])->group(function () {
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase_orders.index');
    Route::get('/purchase-orders/data', [PurchaseOrderController::class, 'data'])->name('purchase_orders.data');
    Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase_orders.create_form');
    Route::get('/purchase-orders/{id}/detail', [PurchaseOrderController::class, 'detail'])->name('purchase_orders.detail')->whereNumber('id');
    Route::get('/purchase-orders/{id}/pdf', [PurchaseOrderController::class, 'pdf'])->name('purchase_orders.pdf')->whereNumber('id');
    Route::get('/purchase-orders/{id}', [PurchaseOrderController::class, 'show'])->name('purchase_orders.show')->whereNumber('id');

    Route::get('/rfqs', [RfqController::class, 'index'])->name('rfqs.index');
    Route::get('/rfqs/data', [RfqController::class, 'data'])->name('rfqs.data');
    Route::get('/rfqs/create', [RfqController::class, 'create'])->name('rfqs.create_form');
    Route::get('/rfqs/{id}/detail', [RfqController::class, 'detail'])->name('rfqs.detail')->whereNumber('id');
    Route::get('/rfqs/{id}', [RfqController::class, 'show'])->name('rfqs.show')->whereNumber('id');
});

Route::middleware(['admin_middle', 'permission:purchase_orders.create'])->group(function () {
    Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase_orders.store');
    Route::post('/rfqs', [RfqController::class, 'store'])->name('rfqs.store');
});

Route::middleware(['admin_middle', 'permission:purchase_orders.edit'])->group(function () {
    Route::put('/purchase-orders/{id}', [PurchaseOrderController::class, 'update'])->name('purchase_orders.update')->whereNumber('id');
    Route::post('/purchase-orders/{id}/items', [PurchaseOrderController::class, 'addItem'])->name('purchase_orders.items.store')->whereNumber('id');
    Route::put('/purchase-orders/{id}/items/{itemId}', [PurchaseOrderController::class, 'updateItem'])->name('purchase_orders.items.update')->whereNumber('id')->whereNumber('itemId');
    Route::delete('/purchase-orders/{id}/items/{itemId}', [PurchaseOrderController::class, 'removeItem'])->name('purchase_orders.items.destroy')->whereNumber('id')->whereNumber('itemId');
    Route::post('/purchase-orders/{id}/send', [PurchaseOrderController::class, 'send'])->name('purchase_orders.send')->whereNumber('id');
    Route::post('/purchase-orders/{id}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase_orders.cancel')->whereNumber('id');
    Route::post('/purchase-orders/{id}/goods-receipts', [GoodsReceiptController::class, 'store'])->name('purchase_orders.goods_receipts.store')->whereNumber('id');

    Route::post('/rfqs/{id}/vendors/{vendorRowId}/quote', [RfqController::class, 'recordQuote'])->name('rfqs.vendors.quote')->whereNumber('id')->whereNumber('vendorRowId');
    Route::post('/rfqs/{id}/convert-to-po', [RfqController::class, 'convertToPo'])->name('rfqs.convert_to_po')->whereNumber('id');
});

Route::middleware(['admin_middle', 'permission:purchase_orders.delete'])->group(function () {
    Route::delete('/purchase-orders/{id}', [PurchaseOrderController::class, 'destroy'])->name('purchase_orders.destroy')->whereNumber('id');
    Route::delete('/rfqs/{id}', [RfqController::class, 'destroy'])->name('rfqs.destroy')->whereNumber('id');
});

Route::middleware(['admin_middle', 'permission:orders.edit'])->group(function () {
    Route::post('/orders/payments', [OrderDetailController::class, 'paymentsStore'])->name('orders.payments.store');
    Route::post('/orders/{id}', [OrderDetailController::class, 'update'])->name('orders.update')->where('id', '[0-9]+');
    Route::post('/projects/update', [ProjectDetailsController::class, 'update_project_details'])->name('projects.update');
});

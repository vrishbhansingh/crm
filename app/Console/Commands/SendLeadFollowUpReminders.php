<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\Tenant;
use App\Notifications\LeadFollowUpReminder;
use App\Support\TenantContext;
use App\Tenancy\TenantConnectionManager;
use App\Support\PermissionTeam;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Lead-follow-up equivalent of SendTaskReminders — same shape, same
 * multi-tenant loop, same "flag once notified" guard, just reading
 * Lead.follow_up_date/time (which aren't cast to Carbon on the model,
 * see DashboardController::leadFollowUpEntry()) instead of Task.remind_at.
 */
class SendLeadFollowUpReminders extends Command
{
    protected $signature = 'crm:send-lead-followup-reminders';

    protected $description = 'Send due in-app reminders for scheduled lead follow-ups';

    public function handle(TenantConnectionManager $connections): int
    {
        $sent = 0;

        if (config('tenancy.mode') === 'shared') {
            $this->sendForActiveTenant($sent);
            $this->info("Sent {$sent} follow-up reminder(s).");

            return self::SUCCESS;
        }

        Tenant::accessible()
            ->where('provision_status', 'ready')
            ->orderBy('id')
            ->each(function (Tenant $tenant) use ($connections, &$sent) {
                // One broken tenant (unprovisioned or out-of-date database)
                // must not stop reminders for every tenant after it.
                try {
                    $connections->activate($tenant);
                } catch (\Throwable $exception) {
                    report($exception);

                    return;
                }

                TenantContext::set($tenant->id);
                PermissionTeam::set($tenant->id);

                try {
                    $this->sendForActiveTenant($sent);
                } catch (\Throwable $exception) {
                    report($exception);
                } finally {
                    TenantContext::clear();
                    PermissionTeam::set(null);
                    $connections->deactivate();
                }
            });

        $this->info("Sent {$sent} follow-up reminder(s).");

        return self::SUCCESS;
    }

    private function sendForActiveTenant(int &$sent): void
    {
        Lead::query()
            ->with(['assignedUser.tenant'])
            ->whereNull('follow_up_notified_at')
            ->whereNotNull('follow_up_date')
            ->where('lead_status', '!=', 'closed')
            ->orderBy('id')
            ->chunkById(100, function ($leads) use (&$sent) {
                foreach ($leads as $lead) {
                    if (! $lead->assignedUser || $lead->assignedUser->status !== 'Active' || $lead->assignedUser->tenant?->status !== 'Active') {
                        continue;
                    }

                    // Follow-up date/time are entered as the company's wall-clock
                    // time, but the app runs in UTC — parse them in the tenant's
                    // own timezone or a 10:00 IST follow-up fires 5.5h late.
                    $timezone = $lead->assignedUser->tenant->timezone ?: config('app.timezone');
                    $followUpAt = Carbon::parse($lead->follow_up_date.($lead->follow_up_time ? ' '.$lead->follow_up_time : ''), $timezone);

                    if ($followUpAt->isFuture()) {
                        continue;
                    }

                    $lead->assignedUser->notify(new LeadFollowUpReminder($lead));
                    $lead->forceFill(['follow_up_notified_at' => now()])->save();
                    $sent++;
                }
            });
    }
}

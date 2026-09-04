<?php

namespace App\Console\Commands;

use App\Models\EmailCampaign;
use App\Models\Tenant;
use App\Services\CampaignSender;
use App\Support\PermissionTeam;
use App\Support\TenantContext;
use App\Tenancy\TenantConnectionManager;
use Illuminate\Console\Command;

class DispatchScheduledCampaigns extends Command
{
    protected $signature = 'crm:dispatch-scheduled-campaigns';

    protected $description = 'Send any email campaign whose scheduled_at time has arrived';

    public function handle(TenantConnectionManager $connections, CampaignSender $sender): int
    {
        $sent = 0;

        if (config('tenancy.mode') === 'shared') {
            $sent += $this->sendDueForActiveTenant($sender);
            $this->info("Dispatched {$sent} scheduled campaign(s).");

            return self::SUCCESS;
        }

        Tenant::accessible()
            ->where('provision_status', 'ready')
            ->orderBy('id')
            ->each(function (Tenant $tenant) use ($connections, $sender, &$sent) {
                $connections->activate($tenant);
                TenantContext::set($tenant->id);
                PermissionTeam::set($tenant->id);

                try {
                    $sent += $this->sendDueForActiveTenant($sender);
                } finally {
                    TenantContext::clear();
                    PermissionTeam::set(null);
                    $connections->deactivate();
                }
            });

        $this->info("Dispatched {$sent} scheduled campaign(s).");

        return self::SUCCESS;
    }

    private function sendDueForActiveTenant(CampaignSender $sender): int
    {
        $count = 0;

        EmailCampaign::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('id')
            ->each(function (EmailCampaign $campaign) use ($sender, &$count) {
                $recipients = $sender->buildRecipients($campaign);

                if ($recipients === 0) {
                    $campaign->forceFill(['status' => 'failed'])->save();

                    return;
                }

                $sender->send($campaign);
                $count++;
            });

        return $count;
    }
}

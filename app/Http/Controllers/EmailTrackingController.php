<?php

namespace App\Http\Controllers;

use App\Models\EmailCampaignEvent;
use App\Models\EmailCampaignRecipient;
use App\Models\Tenant;
use App\Services\CampaignTracker;
use App\Support\TenantContext;
use App\Tenancy\TenantConnectionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Public, unauthenticated open-pixel and click-redirect endpoints for
 * campaign emails. Like the webhooks, there is no session and no tenant
 * middleware: the signed token (see CampaignTracker) names the tenant, and
 * the tenant database is activated by hand just for this one request.
 *
 * Never fails loudly: a bad token still returns the pixel / a 404, and a
 * recording problem is reported but never stops a customer's click from
 * reaching its destination.
 */
class EmailTrackingController extends Controller
{
    // 1x1 transparent GIF.
    private const PIXEL = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    public function open(Request $request, string $token, CampaignTracker $tracker, TenantConnectionManager $connections)
    {
        $data = $tracker->open($token);
        if ($data && $data['k'] === 'o') {
            $this->record($request, $data, 'open', null, $connections);
        }

        return response(base64_decode(self::PIXEL), 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function click(Request $request, string $token, CampaignTracker $tracker, TenantConnectionManager $connections)
    {
        $data = $tracker->open($token);
        abort_unless($data && $data['k'] === 'c' && isset($data['u']), 404);

        $this->record($request, $data, 'click', $data['u'], $connections);

        return redirect()->away($data['u']);
    }

    private function record(Request $request, array $data, string $type, ?string $url, TenantConnectionManager $connections): void
    {
        $activated = false;

        try {
            $tenant = Tenant::find($data['t']);
            if (! $tenant) {
                return;
            }

            if (config('tenancy.mode') !== 'shared') {
                $connections->activate($tenant);
                $activated = true;
            }

            TenantContext::run($tenant->id, function () use ($request, $data, $type, $url) {
                $row = EmailCampaignRecipient::find($data['r']);
                if (! $row) {
                    return;
                }

                EmailCampaignEvent::create([
                    'tenant_id' => $row->tenant_id,
                    'email_campaign_id' => $row->email_campaign_id,
                    'email_campaign_recipient_id' => $row->id,
                    'type' => $type,
                    'url' => $url,
                    'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
                    'created_at' => now(),
                ]);

                $campaign = $row->campaign;

                if ($type === 'click') {
                    $row->increment('click_count');
                    if (! $row->clicked_at) {
                        $row->forceFill(['clicked_at' => now()])->save();
                        $campaign?->increment('clicked_count');
                    }
                    // Clicking proves it was opened, even if images are blocked.
                    if (! $row->opened_at) {
                        $row->forceFill(['opened_at' => now()])->save();
                        $campaign?->increment('opened_count');
                    }

                    return;
                }

                $row->increment('open_count');
                if (! $row->opened_at) {
                    $row->forceFill(['opened_at' => now()])->save();
                    $campaign?->increment('opened_count');
                }
            });
        } catch (\Throwable $exception) {
            report($exception);
        } finally {
            if ($activated) {
                $connections->deactivate();
            }
        }
    }
}

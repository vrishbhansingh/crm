<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\DealStageHistory;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadIntegration;
use App\Models\LeadIntegrationLog;
use App\Models\Pipeline;
use App\Models\User;
use App\Services\LeadIntegrations\LeadPayloadNormalizer;
use App\Services\LeadNumberService;
use App\Support\TenantContext;
use App\Tenancy\TenantConnectionManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Public, unauthenticated endpoint every configured platform posts leads
 * to. Identified only by the opaque `token` in the URL — there's no tenant
 * session here, so the tenant database connection has to be activated by
 * hand for the duration of this one request (see TenantConnectionManager),
 * and torn back down in `finally` regardless of outcome.
 */
class WebhookLeadController extends Controller
{
    private const LEAD_SOURCE_BY_PLATFORM = [
        'website' => 'website',
        'indiamart' => 'indiamart',
        'justdial' => 'justdial',
        'facebook' => 'facebook_ads',
        'google_ads' => 'google_ads',
        'whatsapp' => 'whatsapp',
        'zapier' => 'zapier',
    ];

    public function handle(Request $request, string $token, TenantConnectionManager $connections, LeadPayloadNormalizer $normalizer)
    {
        $integration = LeadIntegration::where('token', $token)->first();
        abort_if(! $integration, 404);

        if ($request->isMethod('get')) {
            return $this->verifyHandshake($request, $integration);
        }

        if (! $integration->is_active) {
            return response()->json(['status' => 'ignored', 'message' => 'This integration is currently disabled.']);
        }

        if (! $this->signatureIsValid($request, $integration)) {
            abort(403, 'Invalid signature.');
        }

        $payload = $request->all();
        $normalized = $normalizer->normalize($integration->platform, $payload, $integration->field_mapping ?? []);

        if (! empty($normalized['external_ref'])
            && LeadIntegrationLog::where('lead_integration_id', $integration->id)
                ->where('external_ref', $normalized['external_ref'])
                ->where('status', 'created')
                ->exists()
        ) {
            $this->log($integration, 'duplicate', $payload, $normalized['external_ref'] ?? null, null, 'Already received this lead before — skipped to avoid a duplicate.');

            return response()->json(['status' => 'duplicate']);
        }

        if (empty($normalized['phone']) && empty($normalized['email'])) {
            $this->log($integration, 'ignored', $payload, $normalized['external_ref'] ?? null, null, 'No phone or email in the payload — nothing to create a lead from.');

            return response()->json(['status' => 'ignored', 'message' => 'No phone or email found in the payload.']);
        }

        // Mirrors ActivateTenantDatabase middleware: in 'shared' tenancy
        // mode every tenant uses the master connection already, and
        // activate() would otherwise hard-throw for a tenant that was never
        // given a real per-tenant database_name.
        if (config('tenancy.mode') !== 'shared') {
            $connections->activate($integration->tenant);
        }

        try {
            [$lead, $deal] = TenantContext::run($integration->tenant_id, function () use ($integration, $normalized) {
                $lead = $this->createLead($integration, $normalized);

                return [$lead, $this->maybeCreateDeal($integration, $lead)];
            });

            $integration->increment('leads_created_count');
            $integration->update(['last_received_at' => now()]);
            $message = 'Lead #'.$lead->lead_number.' created.'.($deal ? ' Added to '.$deal->pipeline->name.' as a new deal.' : '');
            $this->log($integration, 'created', $payload, $normalized['external_ref'] ?? null, $lead->id, $message);

            return response()->json(['status' => 'created', 'lead_id' => $lead->id, 'deal_id' => $deal?->id]);
        } catch (\Throwable $exception) {
            report($exception);
            $this->log($integration, 'failed', $payload, $normalized['external_ref'] ?? null, null, $exception->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Could not process this lead.'], 500);
        } finally {
            $connections->deactivate();
        }
    }

    /**
     * Meta's own verification step for both Lead Ads and WhatsApp Cloud API
     * webhooks: it GETs the URL with hub.verify_token, and the endpoint has
     * to echo hub.challenge back verbatim if the token matches, or the
     * subscription is refused.
     */
    private function verifyHandshake(Request $request, LeadIntegration $integration): Response
    {
        if ($request->query('hub_mode') === 'subscribe' || $request->query('hub.mode') === 'subscribe') {
            $sent = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
            $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

            if ($integration->verify_token && hash_equals($integration->verify_token, (string) $sent)) {
                return response((string) $challenge, 200);
            }

            abort(403, 'Verify token mismatch.');
        }

        return response('This is a lead-capture webhook endpoint. Configure your platform to POST leads here.', 200);
    }

    /**
     * Only Meta platforms sign requests (X-Hub-Signature-256, HMAC-SHA256
     * over the raw body with the App Secret). Every other platform here
     * (IndiaMART, JustDial, generic/Zapier) has no signing scheme at all —
     * the unguessable token in the URL is the only credential they support,
     * so signature checking is skipped whenever no secret was configured.
     */
    private function signatureIsValid(Request $request, LeadIntegration $integration): bool
    {
        if (! $integration->secret) {
            return true;
        }

        $header = $request->header('X-Hub-Signature-256', '');
        if (! str_starts_with($header, 'sha256=')) {
            return false;
        }

        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $integration->secret);

        return hash_equals($expected, $header);
    }

    private function createLead(LeadIntegration $integration, array $normalized): Lead
    {
        $tenant = $integration->tenant;

        // A specific default assignee on the integration always wins over
        // round-robin — that's the whole point of setting one.
        $assignedTo = $integration->default_assigned_to ?: $this->leastLoadedSalesUserId($tenant->id);

        $company = null;
        if (! empty($normalized['company'])) {
            $company = Company::firstOrCreate([
                'tenant_id' => $tenant->id,
                'name' => $normalized['company'],
            ], [
                'owner_id' => $assignedTo,
                'city' => $normalized['city'] ?? null,
                'state' => $normalized['state'] ?? null,
                'status' => 'prospect',
            ]);
        }

        $contact = Contact::create([
            'tenant_id' => $tenant->id,
            'company_id' => $company?->id,
            'owner_id' => $assignedTo,
            'name' => $normalized['name'] ?? 'Unknown',
            'phone' => $normalized['phone'] ?? null,
            'email' => $normalized['email'] ?? null,
            'city' => $normalized['city'] ?? null,
            'is_primary' => true,
            'source' => 'lead',
            'status' => 'active',
        ]);

        $lead = new Lead();
        $lead->tenant_id = $tenant->id;
        $lead->lead_type = $integration->default_lead_type ?: 'inquiry';
        $lead->lead_source = self::LEAD_SOURCE_BY_PLATFORM[$integration->platform] ?? 'other';
        $lead->lead_status = $integration->default_lead_status ?: 'new';
        $lead->priority = $integration->default_priority ?: 'medium';
        $lead->name = $contact->name;
        $lead->phone = $contact->phone;
        $lead->email = $contact->email;
        $lead->city = $normalized['city'] ?? null;
        $lead->state = $normalized['state'] ?? null;
        $lead->product = $normalized['product'] ?? null;
        $lead->requirement = $normalized['message'] ?? null;
        $lead->company_id = $company?->id;
        $lead->company_name = $company?->name;
        $lead->contact_id = $contact->id;
        $lead->assigned_to = $assignedTo;
        $lead->assigned_at = $assignedTo ? now() : null;
        $lead->is_converted = 'No';
        LeadNumberService::saveNew($lead);

        LeadActivity::create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $lead->id,
            'user_id' => null,
            'type' => 'created',
            'description' => 'Lead captured automatically via '.$integration->name.' ('.$integration->platform.').',
        ]);

        if ($assignedTo) {
            LeadActivity::create([
                'tenant_id' => $lead->tenant_id,
                'lead_id' => $lead->id,
                'user_id' => null,
                'type' => 'assigned',
                'description' => ($integration->default_assigned_to ? 'Assigned' : 'Auto-assigned').' to '.optional(User::find($assignedTo))->name,
            ]);
        }

        return $lead;
    }

    /**
     * Opt-in: an integration only gets its leads dropped straight into a
     * sales funnel if a Pipeline was explicitly chosen for it. The starting
     * stage is always the first non-won/non-lost stage by sort order — the
     * same rule LeadDetailController::convertToDeal() uses for a
     * human-triggered lead-to-deal conversion, so a webhook-sourced deal
     * lands exactly where a manually converted one would.
     */
    private function maybeCreateDeal(LeadIntegration $integration, Lead $lead): ?Deal
    {
        if (! $integration->pipeline_id) {
            return null;
        }

        $pipeline = Pipeline::where('tenant_id', $integration->tenant_id)->find($integration->pipeline_id);
        if (! $pipeline) {
            return null;
        }

        $stage = $pipeline->stages()->where('is_won', false)->where('is_lost', false)->orderBy('sort_order')->first();
        if (! $stage) {
            return null;
        }

        $deal = Deal::create([
            'tenant_id' => $lead->tenant_id,
            'pipeline_id' => $pipeline->id,
            'stage_id' => $stage->id,
            'lead_id' => $lead->id,
            'company_id' => $lead->company_id,
            'contact_id' => $lead->contact_id,
            'owner_id' => $lead->assigned_to,
            'name' => trim(($lead->company_name ?: $lead->name).' - Deal'),
            'amount' => 0,
        ]);

        DealStageHistory::create([
            'deal_id' => $deal->id,
            'from_stage_id' => null,
            'to_stage_id' => $stage->id,
            'changed_by' => null,
        ]);

        $lead->is_converted = 'Yes';
        $lead->converted_at = now();
        $lead->conversion_value = 0;
        $lead->save();

        LeadActivity::create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $lead->id,
            'user_id' => null,
            'type' => 'converted',
            'description' => 'Automatically added to '.$pipeline->name.' ('.$stage->name.') via '.$integration->name.'.',
        ]);

        return $deal;
    }

    /**
     * Mirrors LeadController::leastLoadedSalesUserId() — kept as a separate
     * copy rather than shared, since this runs in an unauthenticated,
     * manually-activated tenant-connection context quite different from a
     * normal request, and the two call sites shouldn't be coupled just to
     * save a few lines.
     */
    private function leastLoadedSalesUserId(?int $tenantId): ?int
    {
        if ($tenantId === null) {
            return null;
        }

        $candidate = User::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'Active')
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Sales Executive', 'Sales Manager']))
            ->withCount(['leads' => fn ($q) => $q->where('status', 'Active')])
            ->orderBy('leads_count')
            ->first();

        return $candidate?->id;
    }

    private function log(LeadIntegration $integration, string $status, array $payload, ?string $externalRef, ?int $leadId, ?string $message): void
    {
        LeadIntegrationLog::create([
            'lead_integration_id' => $integration->id,
            'tenant_id' => $integration->tenant_id,
            'status' => $status,
            'external_ref' => $externalRef,
            'created_lead_id' => $leadId,
            'message' => $message,
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }
}

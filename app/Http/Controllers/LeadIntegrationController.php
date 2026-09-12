<?php

namespace App\Http\Controllers;

use App\Models\LeadIntegration;
use App\Models\MasterValue;
use App\Models\Pipeline;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LeadIntegrationController extends Controller
{
    private const PLATFORMS = ['website', 'indiamart', 'justdial', 'facebook', 'google_ads', 'whatsapp', 'zapier'];

    private const MAPPABLE_FIELDS = ['name', 'phone', 'email', 'company', 'city', 'state', 'message'];

    /**
     * Setup steps researched directly from each platform's own
     * documentation — IndiaMART's Push API help article, Meta's Lead Ads
     * and WhatsApp Cloud API webhook docs, and how JustDial's webhook
     * onboarding actually works (account-manager-configured, no self-serve
     * UI). Google Ads and Facebook don't offer a plain "paste a URL" option
     * without either a Meta-approved developer app or a Zapier/Make bridge,
     * so both paths are given rather than pretending only one exists.
     */
    private const PLATFORM_META = [
        'website' => [
            'label' => 'Website / Custom Form',
            'icon' => 'fa-globe',
            'description' => 'Any form on your own website — send its submissions straight into your CRM as leads.',
            'mappable' => true,
            'steps' => [
                'Copy your unique webhook URL below.',
                "In your website's form handler, configure it to send an HTTP POST with the visitor's name, phone, and email as JSON or form fields to that URL.",
                "That's it — every submission becomes a lead here automatically.",
            ],
        ],
        'indiamart' => [
            'label' => 'IndiaMART',
            'icon' => 'fa-industry',
            'description' => "Real-time leads from your IndiaMART seller account via their official Lead Manager Push API.",
            'mappable' => false,
            'steps' => [
                'Log in to your IndiaMART seller account and open Lead Manager.',
                "From the three-dot menu, choose Import/Export Leads \u{2192} Push API.",
                "Select \u{201c}Others\u{201d} as the CRM platform, then paste the Webhook Listener URL below.",
                'Confirm the OTP sent to your registered mobile number to activate it.',
                'New IndiaMART leads will start arriving here in real time.',
            ],
        ],
        'justdial' => [
            'label' => 'JustDial',
            'icon' => 'fa-phone-square',
            'description' => 'Leads from calls and enquiries on your JustDial listing.',
            'mappable' => true,
            'steps' => [
                'Copy the webhook URL below.',
                "JustDial doesn't offer self-service webhook setup \u{2014} share this URL with your JustDial account manager and ask them to enable webhook lead push for your account.",
                'Once they confirm it, new JustDial leads will start arriving here automatically.',
            ],
        ],
        'facebook' => [
            'label' => 'Facebook Lead Ads',
            'icon' => 'fa-facebook-square',
            'description' => "Leads submitted through your Facebook/Instagram lead ad forms.",
            'mappable' => true,
            'steps' => [
                'Easiest, works today: connect a free Zapier or Make.com "Facebook Lead Ads \u{2192} Webhook" automation and paste the URL below as its destination.',
                'If you already have a Meta Developer App approved for Lead Ads, you can instead subscribe its Page webhook directly to this URL, using the Verify Token shown below during setup.',
                'Leads will appear here automatically once either is connected.',
            ],
        ],
        'google_ads' => [
            'label' => 'Google Ads Lead Form',
            'icon' => 'fa-google',
            'description' => 'Leads submitted through your Google Ads lead form extensions.',
            'mappable' => true,
            'steps' => [
                "Google doesn't offer a direct webhook to a custom URL from its own dashboard \u{2014} the reliable way is a free Zapier or Make.com \"Google Ads Lead Form \u{2192} Webhook\" automation.",
                'Paste the URL below as that automation\'s webhook destination.',
                'New Google lead form submissions will start arriving here automatically.',
            ],
        ],
        'whatsapp' => [
            'label' => 'WhatsApp Business',
            'icon' => 'fa-whatsapp',
            'description' => 'Incoming WhatsApp messages, captured as leads automatically.',
            'mappable' => false,
            'steps' => [
                'This requires a WhatsApp Business Platform (Cloud API) app, usually set up through Meta or a Business Solution Provider.',
                "In that app's webhook settings, paste the URL below and use the Verify Token shown here to complete verification.",
                'Subscribe to the "messages" field \u{2014} every incoming WhatsApp message will be captured here as a lead.',
            ],
        ],
        'zapier' => [
            'label' => 'Zapier / Make (generic)',
            'icon' => 'fa-bolt',
            'description' => "Works with any of Zapier's or Make's thousands of connected apps \u{2014} Google Sheets, Typeform, LinkedIn Lead Gen, and more.",
            'mappable' => true,
            'steps' => [
                'Copy the webhook URL below.',
                'In Zapier or Make, add a "Webhooks" action step and paste this URL as the destination.',
                "Map the lead's name, phone, and email fields from whatever app you're connecting \u{2014} this one URL works with any of them.",
            ],
        ],
    ];

    public function index()
    {
        $tenantId = TenantContext::id();
        $integrations = LeadIntegration::where('tenant_id', $tenantId)->latest()->get();
        $countsByPlatform = $integrations->countBy('platform');
        $platforms = self::PLATFORM_META;

        $pipelines = Pipeline::where('is_active', true)->orderBy('sort_order')->get(['id', 'name']);
        $users = User::where('tenant_id', $tenantId)->where('status', 'Active')->orderBy('name')->get(['id', 'name']);
        $leadTypes = MasterValue::options('lead_type');
        $leadStatuses = MasterValue::options('lead_status');
        $leadPriorities = MasterValue::options('lead_priority');
        $mappableFields = self::MAPPABLE_FIELDS;

        return view('integrations.index', compact(
            'integrations', 'countsByPlatform', 'platforms', 'pipelines', 'users',
            'leadTypes', 'leadStatuses', 'leadPriorities', 'mappableFields'
        ));
    }

    public function store(Request $request)
    {
        $tenantId = TenantContext::id();
        $data = $this->validated($request, $tenantId);

        $integration = LeadIntegration::create([
            'tenant_id' => $tenantId,
            'platform' => $data['platform'],
            'name' => $data['name'],
            'token' => LeadIntegration::generateToken(),
            'verify_token' => in_array($data['platform'], ['facebook', 'whatsapp'], true) ? Str::random(32) : null,
            'created_by' => Auth::guard('web')->id(),
            'default_lead_type' => $data['default_lead_type'] ?? null,
            'default_lead_status' => $data['default_lead_status'] ?? null,
            'default_priority' => $data['default_priority'] ?? null,
            'default_assigned_to' => $data['default_assigned_to'] ?? null,
            'pipeline_id' => $data['pipeline_id'] ?? null,
            'field_mapping' => $this->cleanMapping($request->input('field_mapping', [])),
        ]);

        return response()->json(['status' => true, 'message' => 'Webhook created.', 'data' => ['id' => $integration->id]]);
    }

    public function update(Request $request, LeadIntegration $integration)
    {
        $this->authorizeIntegration($integration);
        $data = $this->validated($request, $integration->tenant_id, $integration);

        $update = collect($data)->only([
            'name', 'default_lead_type', 'default_lead_status', 'default_priority', 'default_assigned_to', 'pipeline_id',
        ])->all();

        if ($request->has('is_active')) {
            $update['is_active'] = $request->boolean('is_active');
        }
        if ($request->has('field_mapping')) {
            $update['field_mapping'] = $this->cleanMapping($request->input('field_mapping', []));
        }

        $integration->update($update);

        return response()->json(['status' => true, 'message' => 'Webhook updated.']);
    }

    public function regenerate(LeadIntegration $integration)
    {
        $this->authorizeIntegration($integration);

        $integration->update([
            'token' => LeadIntegration::generateToken(),
            'verify_token' => in_array($integration->platform, ['facebook', 'whatsapp'], true) ? Str::random(32) : $integration->verify_token,
        ]);

        return response()->json(['status' => true, 'message' => 'A new webhook URL was generated — update it on the platform side too.', 'data' => ['webhook_url' => $integration->webhookUrl()]]);
    }

    public function destroy(LeadIntegration $integration)
    {
        $this->authorizeIntegration($integration);
        $integration->delete();

        return response()->json(['status' => true, 'message' => 'Webhook removed.']);
    }

    public function logs(LeadIntegration $integration)
    {
        $this->authorizeIntegration($integration);
        $logs = $integration->logs()->limit(50)->get();

        return response()->json(['status' => true, 'data' => $logs]);
    }

    public function show(LeadIntegration $integration)
    {
        $this->authorizeIntegration($integration);

        return response()->json(['status' => true, 'data' => $integration]);
    }

    private function validated(Request $request, int $tenantId, ?LeadIntegration $existing = null): array
    {
        return $request->validate([
            'platform' => [$existing ? 'sometimes' : 'required', Rule::in(self::PLATFORMS)],
            'name' => ['required', 'string', 'max:150'],
            'default_lead_type' => ['nullable', 'string', Rule::in(MasterValue::options('lead_type')->pluck('code'))],
            'default_lead_status' => ['nullable', 'string', Rule::in(MasterValue::options('lead_status')->pluck('code'))],
            'default_priority' => ['nullable', 'string', Rule::in(MasterValue::options('lead_priority')->pluck('code'))],
            'default_assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')->where('tenant_id', $tenantId)],
            'pipeline_id' => ['nullable', 'integer', Rule::exists('pipelines', 'id')->where('tenant_id', $tenantId)],
        ]);
    }

    /**
     * Drops empty rows (a user who added a mapping row then left it blank)
     * and only keeps keys this app actually knows what to do with.
     */
    private function cleanMapping(mixed $mapping): array
    {
        if (! is_array($mapping)) {
            return [];
        }

        $clean = [];
        foreach ($mapping as $field => $incomingKey) {
            if (in_array($field, self::MAPPABLE_FIELDS, true) && is_string($incomingKey) && trim($incomingKey) !== '') {
                $clean[$field] = trim($incomingKey);
            }
        }

        return $clean;
    }

    private function authorizeIntegration(LeadIntegration $integration): void
    {
        abort_unless($integration->tenant_id === TenantContext::id(), 404);
    }
}

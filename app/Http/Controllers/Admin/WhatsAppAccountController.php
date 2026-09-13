<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappAccount;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Settings page: connect/manage the tenant's WhatsApp send channels —
 * either the official Meta Cloud API, or a generic "unofficial" gateway
 * (API key + sender number). WhatsappAccount is a central-connection model
 * (same reasoning as LeadIntegration — the public inbound webhook has to
 * resolve a tenant before any tenant DB is active), so route-model-binding
 * is safe here regardless of middleware order.
 */
class WhatsAppAccountController extends Controller
{
    public function index()
    {
        $tenantId = TenantContext::id();
        $accounts = WhatsappAccount::where('tenant_id', $tenantId)->latest()->get();

        return view('whatsapp.settings', compact('accounts'));
    }

    public function store(Request $request)
    {
        $tenantId = TenantContext::id();
        $data = $this->validated($request);

        $account = WhatsappAccount::create([
            'tenant_id' => $tenantId,
            'channel_type' => $data['channel_type'],
            'name' => $data['name'],
            'phone_number' => $data['phone_number'] ?? null,
            'webhook_token' => WhatsappAccount::generateWebhookToken(),
            'verify_token' => $data['channel_type'] === 'meta_cloud' ? \Illuminate\Support\Str::random(32) : null,
            'credentials' => $this->credentialsFrom($data, $data['channel_type']),
            'is_default' => ! WhatsappAccount::where('tenant_id', $tenantId)->exists(),
            'created_by' => Auth::guard('web')->id(),
        ]);

        return response()->json(['status' => true, 'message' => 'WhatsApp account connected.', 'data' => ['id' => $account->id]]);
    }

    public function update(Request $request, WhatsappAccount $account)
    {
        $this->authorizeAccount($account);
        $data = $this->validated($request, $account);

        $update = collect($data)->only(['name', 'phone_number'])->all();
        $update['credentials'] = array_merge($account->credentials ?? [], $this->credentialsFrom($data, $account->channel_type, skipEmpty: true));

        if ($request->has('is_active')) {
            $update['is_active'] = $request->boolean('is_active');
        }

        $account->update($update);

        if ($request->boolean('make_default')) {
            WhatsappAccount::where('tenant_id', $account->tenant_id)->where('id', '!=', $account->id)->update(['is_default' => false]);
            $account->update(['is_default' => true]);
        }

        return response()->json(['status' => true, 'message' => 'WhatsApp account updated.']);
    }

    public function regenerate(WhatsappAccount $account)
    {
        $this->authorizeAccount($account);

        $account->update([
            'webhook_token' => WhatsappAccount::generateWebhookToken(),
            'verify_token' => $account->channel_type === 'meta_cloud' ? \Illuminate\Support\Str::random(32) : $account->verify_token,
        ]);

        return response()->json(['status' => true, 'message' => 'A new webhook URL was generated — update it on the provider side too.', 'data' => ['webhook_url' => $account->webhookUrl()]]);
    }

    public function destroy(WhatsappAccount $account)
    {
        $this->authorizeAccount($account);
        $account->delete();

        return response()->json(['status' => true, 'message' => 'WhatsApp account removed.']);
    }

    public function logs(WhatsappAccount $account)
    {
        $this->authorizeAccount($account);
        $logs = $account->logs()->limit(50)->get();

        return response()->json(['status' => true, 'data' => $logs]);
    }

    public function show(WhatsappAccount $account)
    {
        $this->authorizeAccount($account);

        return response()->json(['status' => true, 'data' => array_merge($account->toArray(), [
            'credentials' => $account->credentials ?? [],
        ])]);
    }

    private function validated(Request $request, ?WhatsappAccount $existing = null): array
    {
        return $request->validate([
            'channel_type' => [$existing ? 'sometimes' : 'required', Rule::in(['meta_cloud', 'unofficial'])],
            'name' => ['required', 'string', 'max:150'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            // meta_cloud
            'phone_number_id' => ['nullable', 'string', 'max:60'],
            'waba_id' => ['nullable', 'string', 'max:60'],
            'access_token' => ['nullable', 'string'],
            'app_secret' => ['nullable', 'string'],
            // unofficial
            'api_url' => ['nullable', 'url', 'max:500'],
            'api_key' => ['nullable', 'string'],
            'api_key_location' => ['nullable', Rule::in(['query', 'header', 'body'])],
            'api_key_param' => ['nullable', 'string', 'max:60'],
            'http_method' => ['nullable', Rule::in(['post', 'get'])],
        ]);
    }

    /**
     * Only the fields relevant to the chosen channel_type are stored; on
     * update ($skipEmpty), a blank secret field means "leave it as is"
     * rather than wiping a previously saved token.
     */
    private function credentialsFrom(array $data, ?string $channel, bool $skipEmpty = false): array
    {
        $keys = $channel === 'unofficial'
            ? ['api_url', 'api_key', 'api_key_location', 'api_key_param', 'http_method']
            : ['phone_number_id', 'waba_id', 'access_token', 'app_secret'];

        $credentials = [];
        foreach ($keys as $key) {
            $value = $data[$key] ?? null;
            if ($skipEmpty && ($value === null || $value === '')) {
                continue;
            }
            $credentials[$key] = $value;
        }

        return $credentials;
    }

    private function authorizeAccount(WhatsappAccount $account): void
    {
        abort_unless($account->tenant_id === TenantContext::id(), 404);
    }
}

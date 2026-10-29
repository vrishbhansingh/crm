<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiConfiguration;
use App\Services\Ai\AiGateway;
use App\Services\Ai\AiProviders;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Company-wise AI settings: several API-key configurations (any mix of
 * providers), tried in priority order with automatic failover (see
 * AiConfigResolver / AiGateway). Keys are encrypted at rest and never
 * returned to the browser — only a "…abcd" hint.
 */
class AiSettingsController extends Controller
{
    public function index()
    {
        return view('ai_settings.index', [
            'serverDefault' => filled(config('ai.api_key')),
            'enabled' => (bool) config('ai.enabled'),
            'providers' => AiProviders::forUi(),
            'pricedAt' => AiProviders::PRICED_AT,
            'sampleIn' => AiProviders::SAMPLE_INPUT_TOKENS,
            'sampleOut' => AiProviders::SAMPLE_OUTPUT_TOKENS,
        ]);
    }

    public function data()
    {
        $rows = AiConfiguration::where('tenant_id', TenantContext::id())->orderBy('priority')->orderBy('id')->get()
            ->map(function (AiConfiguration $c) {
                $provider = $c->provider ?: 'anthropic';
                $info = AiProviders::get($provider);
                $model = $c->model ?: ($provider === 'anthropic' ? (string) config('ai.model') : AiProviders::defaultModel($provider));
                $modelInfo = AiProviders::modelInfo($provider, $model);

                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'provider' => $provider,
                    'provider_label' => $info['label'] ?? $provider,
                    'provider_color' => $info['color'] ?? '#64748b',
                    'provider_initial' => $info['initial'] ?? '?',
                    'key_hint' => $c->key_hint,
                    'notify_email' => $c->notify_email,
                    'model' => $c->model,
                    'model_label' => $modelInfo['label'] ?? $model,
                    'tier' => $modelInfo['tier'] ?? null,
                    'priority' => $c->priority,
                    'is_active' => $c->is_active,
                    'status' => $c->status,
                    'last_error' => $c->last_error,
                    'last_tested_at' => optional($c->last_tested_at)->diffForHumans(),
                    'last_used_at' => optional($c->last_used_at)->diffForHumans(),
                    'requests' => (int) $c->requests_count,
                    'input_tokens' => (int) $c->input_tokens,
                    'output_tokens' => (int) $c->output_tokens,
                    'est_cost' => AiProviders::cost($provider, $model, (int) $c->input_tokens, (int) $c->output_tokens),
                ];
            });

        return response()->json(['status' => true, 'data' => $rows]);
    }

    public function store(Request $request)
    {
        $tenantId = TenantContext::id();
        $data = $this->validated($request, creating: true);

        $config = AiConfiguration::create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'provider' => $data['provider'],
            'api_key' => $data['api_key'],
            'key_hint' => AiConfiguration::hintFor($data['api_key']),
            'notify_email' => $data['notify_email'] ?? null,
            'model' => $data['model'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'priority' => (int) AiConfiguration::where('tenant_id', $tenantId)->max('priority') + 1,
            'created_by' => Auth::guard('web')->id(),
        ]);

        return response()->json(['status' => true, 'message' => 'Configuration saved. Press Test to check the key.', 'data' => ['id' => $config->id]]);
    }

    public function update(Request $request, $id)
    {
        $config = $this->mine($id);
        $data = $this->validated($request, creating: false);

        $update = [
            'name' => $data['name'],
            'provider' => $data['provider'],
            'notify_email' => $data['notify_email'] ?? null,
            'model' => $data['model'] ?? null,
        ];
        if ($request->has('is_active')) {
            $update['is_active'] = $request->boolean('is_active');
        }
        // Blank = keep the saved key. A new key — or a different provider or model — resets health so it gets a fresh start.
        $changed = ! empty($data['api_key']) || $data['provider'] !== ($config->provider ?: 'anthropic') || ($data['model'] ?? null) !== $config->model;
        if (! empty($data['api_key'])) {
            $update += ['api_key' => $data['api_key'], 'key_hint' => AiConfiguration::hintFor($data['api_key'])];
        }
        if ($changed) {
            $update += ['status' => 'untested', 'last_error' => null, 'failed_at' => null, 'notified_at' => null];
        }
        $config->update($update);

        return response()->json(['status' => true, 'message' => 'Configuration updated.']);
    }

    public function destroy($id)
    {
        $this->mine($id)->delete();

        return response()->json(['status' => true, 'message' => 'Configuration removed.']);
    }

    /** One tiny real call with exactly this key (no failover). */
    public function test($id)
    {
        $result = app(AiGateway::class)->ping($this->mine($id));

        return response()->json(['status' => $result['ok'], 'message' => $result['message']]);
    }

    /** Swap priority with the neighbouring configuration. direction: up | down */
    public function move(Request $request, $id)
    {
        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];
        $config = $this->mine($id);

        $siblings = AiConfiguration::where('tenant_id', $config->tenant_id)->orderBy('priority')->orderBy('id')->get()->values();
        $index = $siblings->search(fn ($c) => $c->id === $config->id);
        $swapWith = $siblings->get($direction === 'up' ? $index - 1 : $index + 1);

        if ($swapWith && $index !== false) {
            // Renumber 1..n so equal priorities can never leave the order ambiguous.
            $order = $siblings->all();
            $other = $index + ($direction === 'up' ? -1 : 1);
            [$order[$index], $order[$other]] = [$order[$other], $order[$index]];
            foreach ($order as $position => $row) {
                AiConfiguration::whereKey($row->id)->update(['priority' => $position + 1]);
            }
        }

        return response()->json(['status' => true]);
    }

    private function validated(Request $request, bool $creating): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'provider' => ['required', Rule::in(array_keys(AiProviders::all()))],
            'api_key' => [$creating ? 'required' : 'nullable', 'string', 'min:16', 'max:300', 'regex:/^\S+$/'],
            'notify_email' => ['nullable', 'email', 'max:190'],
            'model' => ['nullable', 'string', 'max:80', 'regex:/^[A-Za-z0-9.:_\/\-]+$/'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // OpenRouter has no default model, so one must be named.
        if ((AiProviders::get($data['provider'])['custom_only'] ?? false) && blank($data['model'] ?? null)) {
            abort(response()->json(['message' => 'Enter a model id for this provider.', 'errors' => ['model' => ['Enter a model id for this provider.']]], 422));
        }

        return $data;
    }

    private function mine($id): AiConfiguration
    {
        $config = AiConfiguration::findOrFail($id);
        abort_unless($config->tenant_id === TenantContext::id(), 404);

        return $config;
    }
}

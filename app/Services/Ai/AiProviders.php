<?php

namespace App\Services\Ai;

/**
 * The AI providers a company can connect, with their key-signup links and
 * the models worth offering. Prices are INDICATIVE (USD per 1M tokens, as of
 * `PRICED_AT`) so people can compare what each choice is likely to cost; the
 * provider's own pricing page is the source of truth and is linked from the
 * screen. Anything not listed can still be used via "custom model id".
 *
 * Claude prices come from Anthropic's published rates; the others are the
 * figures most consistently reported at the time of writing and may move.
 */
class AiProviders
{
    public const PRICED_AT = 'Oct 2026';

    /** A typical assistant question (system prompt + tools + CRM data in, a short answer out). */
    public const SAMPLE_INPUT_TOKENS = 6000;
    public const SAMPLE_OUTPUT_TOKENS = 500;

    public static function all(): array
    {
        return [
            'anthropic' => [
                'label' => 'Claude', 'vendor' => 'Anthropic', 'color' => '#c96442', 'initial' => 'C',
                'tagline' => 'Strongest reasoning and tool use',
                'driver' => 'anthropic', 'free_tier' => false,
                'key_url' => 'https://console.anthropic.com/settings/keys',
                'pricing_url' => 'https://www.anthropic.com/pricing#api',
                'key_hint' => 'Starts with sk-ant-',
                'default_model' => 'claude-sonnet-5-5', 'guard_model' => 'claude-haiku-5-5',
                'models' => [
                    ['id' => 'claude-haiku-5-5', 'label' => 'Haiku 5.5', 'tier' => 'Economy', 'in' => 0.10, 'out' => 0.50, 'note' => 'Fastest and cheapest. Good for simple questions.'],
                    ['id' => 'claude-sonnet-5-5', 'label' => 'Sonnet 5.5', 'tier' => 'Balanced', 'in' => 2.00, 'out' => 10.00, 'note' => 'Best everyday balance of quality and cost.'],
                    ['id' => 'claude-opus-5-5', 'label' => 'Opus 5.5', 'tier' => 'Premium', 'in' => 4.00, 'out' => 20.00, 'note' => 'Most capable for complex analysis.'],
                ],
            ],
            'openai' => [
                'label' => 'OpenAI', 'vendor' => 'OpenAI', 'color' => '#10a37f', 'initial' => 'O',
                'tagline' => 'GPT models, widely used',
                'driver' => 'openai', 'base_url' => 'https://api.openai.com/v1', 'free_tier' => false, 'token_param' => 'max_completion_tokens',
                'key_url' => 'https://platform.openai.com/api-keys',
                'pricing_url' => 'https://openai.com/api/pricing/',
                'key_hint' => 'Starts with sk-',
                'default_model' => 'gpt-5.4-mini', 'guard_model' => 'gpt-5.4-nano',
                'models' => [
                    ['id' => 'gpt-5.4-nano', 'label' => 'GPT-5.4 nano', 'tier' => 'Economy', 'in' => 0.20, 'out' => 1.25, 'note' => 'Cheapest current GPT.'],
                    ['id' => 'gpt-5.4-mini', 'label' => 'GPT-5.4 mini', 'tier' => 'Balanced', 'in' => 0.75, 'out' => 4.50, 'note' => 'Good balance for an assistant.'],
                    ['id' => 'gpt-4.1-mini', 'label' => 'GPT-4.1 mini', 'tier' => 'Economy', 'in' => 0.40, 'out' => 1.60, 'note' => 'Previous generation, still reliable.'],
                ],
            ],
            'gemini' => [
                'label' => 'Gemini', 'vendor' => 'Google', 'color' => '#4285f4', 'initial' => 'G',
                'tagline' => 'Generous free tier, fast',
                'driver' => 'openai', 'base_url' => 'https://generativelanguage.googleapis.com/v1beta/openai', 'free_tier' => true, 'token_param' => 'max_tokens',
                'key_url' => 'https://aistudio.google.com/apikey',
                'pricing_url' => 'https://ai.google.dev/gemini-api/docs/pricing',
                'key_hint' => 'Starts with AIza',
                'default_model' => 'gemini-2.5-flash', 'guard_model' => 'gemini-2.5-flash-lite',
                'models' => [
                    ['id' => 'gemini-2.5-flash-lite', 'label' => '2.5 Flash-Lite', 'tier' => 'Economy', 'in' => 0.10, 'out' => 0.40, 'note' => 'Cheapest. Free tier available.'],
                    ['id' => 'gemini-2.5-flash', 'label' => '2.5 Flash', 'tier' => 'Balanced', 'in' => 0.30, 'out' => 2.50, 'note' => 'Fast and capable. Free tier available.'],
                    ['id' => 'gemini-2.5-pro', 'label' => '2.5 Pro', 'tier' => 'Premium', 'in' => 1.25, 'out' => 10.00, 'note' => 'Best Gemini quality for hard questions.'],
                ],
            ],
            'groq' => [
                'label' => 'Groq', 'vendor' => 'Groq', 'color' => '#f55036', 'initial' => 'G',
                'tagline' => 'Very fast open models, free tier',
                'driver' => 'openai', 'base_url' => 'https://api.groq.com/openai/v1', 'free_tier' => true, 'token_param' => 'max_tokens',
                'key_url' => 'https://console.groq.com/keys',
                'pricing_url' => 'https://groq.com/pricing',
                'key_hint' => 'Starts with gsk_',
                'default_model' => 'openai/gpt-oss-120b', 'guard_model' => 'openai/gpt-oss-20b',
                'models' => [
                    ['id' => 'openai/gpt-oss-20b', 'label' => 'GPT-OSS 20B', 'tier' => 'Economy', 'in' => 0.075, 'out' => 0.30, 'note' => 'Quickest and cheapest.'],
                    ['id' => 'openai/gpt-oss-120b', 'label' => 'GPT-OSS 120B', 'tier' => 'Balanced', 'in' => 0.15, 'out' => 0.60, 'note' => 'Strong open model at a low price.'],
                    ['id' => 'llama-3.3-70b-versatile', 'label' => 'Llama 3.3 70B', 'tier' => 'Balanced', 'in' => 0.59, 'out' => 0.79, 'note' => 'May no longer be on every plan; Test it first.'],
                ],
            ],
            'mistral' => [
                'label' => 'Mistral', 'vendor' => 'Mistral AI', 'color' => '#fa520f', 'initial' => 'M',
                'tagline' => 'European provider, good value',
                'driver' => 'openai', 'base_url' => 'https://api.mistral.ai/v1', 'free_tier' => false, 'token_param' => 'max_tokens',
                'key_url' => 'https://console.mistral.ai/api-keys',
                'pricing_url' => 'https://mistral.ai/pricing#api-pricing',
                'key_hint' => 'From the Mistral console',
                'default_model' => 'mistral-small-latest', 'guard_model' => 'mistral-small-latest',
                'models' => [
                    ['id' => 'mistral-small-latest', 'label' => 'Mistral Small', 'tier' => 'Economy', 'in' => null, 'out' => null, 'note' => 'Low cost; check Mistral pricing.'],
                    ['id' => 'mistral-medium-latest', 'label' => 'Mistral Medium', 'tier' => 'Balanced', 'in' => null, 'out' => null, 'note' => 'Balanced; check Mistral pricing.'],
                    ['id' => 'mistral-large-latest', 'label' => 'Mistral Large', 'tier' => 'Premium', 'in' => null, 'out' => null, 'note' => 'Most capable; check Mistral pricing.'],
                ],
            ],
            'deepseek' => [
                'label' => 'DeepSeek', 'vendor' => 'DeepSeek', 'color' => '#4d6bfe', 'initial' => 'D',
                'tagline' => 'Very low prices',
                'driver' => 'openai', 'base_url' => 'https://api.deepseek.com', 'free_tier' => false, 'token_param' => 'max_tokens',
                'key_url' => 'https://platform.deepseek.com/api_keys',
                'pricing_url' => 'https://api-docs.deepseek.com/quick_start/pricing',
                'key_hint' => 'Starts with sk-',
                'default_model' => 'deepseek-chat', 'guard_model' => 'deepseek-chat',
                'models' => [
                    ['id' => 'deepseek-chat', 'label' => 'DeepSeek Chat', 'tier' => 'Economy', 'in' => null, 'out' => null, 'note' => 'Very low cost; check DeepSeek pricing.'],
                ],
            ],
            'openrouter' => [
                'label' => 'OpenRouter', 'vendor' => 'OpenRouter', 'color' => '#6467f2', 'initial' => 'R',
                'tagline' => 'One key, hundreds of models',
                'driver' => 'openai', 'base_url' => 'https://openrouter.ai/api/v1', 'free_tier' => true, 'token_param' => 'max_tokens',
                'key_url' => 'https://openrouter.ai/keys',
                'pricing_url' => 'https://openrouter.ai/models',
                'key_hint' => 'Starts with sk-or-',
                'default_model' => '', 'guard_model' => '',
                'models' => [],
                'custom_only' => true,
            ],
        ];
    }

    public static function get(?string $provider): ?array
    {
        return self::all()[$provider ?: 'anthropic'] ?? null;
    }

    public static function exists(string $provider): bool
    {
        return isset(self::all()[$provider]);
    }

    public static function defaultModel(string $provider): string
    {
        return (string) (self::get($provider)['default_model'] ?? '');
    }

    /** The cheap model used for the CRM-only topic check on that provider. */
    public static function guardModel(string $provider, ?string $fallback = null): string
    {
        $model = (string) (self::get($provider)['guard_model'] ?? '');

        return $model !== '' ? $model : (string) $fallback;
    }

    public static function modelInfo(string $provider, ?string $modelId): ?array
    {
        foreach ((self::get($provider)['models'] ?? []) as $model) {
            if ($model['id'] === $modelId) {
                return $model;
            }
        }

        return null;
    }

    /** Estimated USD for the given token counts, or null if this model has no known price. */
    public static function cost(string $provider, ?string $modelId, int $inputTokens, int $outputTokens): ?float
    {
        $info = self::modelInfo($provider, $modelId ?: self::defaultModel($provider));

        return $info && $info['in'] !== null && $info['out'] !== null
            ? ($inputTokens * $info['in'] + $outputTokens * $info['out']) / 1_000_000
            : null;
    }

    /** Estimated USD for 1,000 typical assistant questions, or null if unknown. */
    public static function costPer1000Questions(string $provider, ?string $modelId): ?float
    {
        $one = self::cost($provider, $modelId, self::SAMPLE_INPUT_TOKENS, self::SAMPLE_OUTPUT_TOKENS);

        return $one === null ? null : round($one * 1000, 2);
    }

    /** What the settings screen needs: everything above, with the per-1000-questions estimate worked out. */
    public static function forUi(): array
    {
        $out = [];
        foreach (self::all() as $key => $provider) {
            $provider['key'] = $key;
            $provider['models'] = array_map(fn ($m) => $m + ['per_1000' => self::costPer1000Questions($key, $m['id'])], $provider['models']);
            $out[] = $provider;
        }

        return $out;
    }
}

<?php

return [
    // Master switch. The assistant also stays hidden when no API key is set.
    'enabled' => env('AI_ASSISTANT_ENABLED', true),

    'api_key' => env('ANTHROPIC_API_KEY'),

    // Model and effort are env-tunable so cost/quality can be changed
    // without a deploy. "low" effort keeps chat answers fast; raise it if
    // answers to analytical questions need more depth.
    'model' => env('AI_MODEL', 'claude-opus-5-5'),

    // The CRM-only gate: a cheap tool-less check that runs BEFORE the assistant and
    // refuses anything that isn't about this CRM. Switch off only for debugging.
    'scope_guard' => (bool) env('AI_SCOPE_GUARD', true),
    'guard_model' => env('AI_GUARD_MODEL', 'claude-haiku-5-5'),
    'effort' => env('AI_EFFORT', 'low'),
    'max_tokens' => (int) env('AI_MAX_TOKENS', 2000),
    'timeout' => (int) env('AI_TIMEOUT', 45),           // seconds per API call
    'ca_bundle' => env('AI_CA_BUNDLE'),                 // optional path to a cacert.pem if PHP has none configured

    // Hard safety rails.
    'max_tool_rounds' => 6,          // tool-call loops per question
    'history_messages' => 16,        // prior chat messages kept per user session
    'max_question_length' => 1000,
    'bot_daily_replies' => (int) env('AI_BOT_DAILY_REPLIES', 300), // WhatsApp bot replies per account per day
    'daily_message_limit' => (int) env('AI_DAILY_MESSAGE_LIMIT', 100), // per user
];

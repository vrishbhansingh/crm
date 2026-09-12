<?php

namespace App\Services\LeadIntegrations;

/**
 * Turns one platform's webhook payload into a flat, common shape:
 * ['name','phone','email','company','city','state','message','external_ref'].
 * Every key is optional in the return value — the caller decides whether
 * what's left is enough to actually create a lead.
 *
 * Field formats researched directly from each platform's own docs (not
 * guessed): IndiaMART's Push API help article (SENDER_* / QUERY_* fields,
 * UNIQUE_QUERY_ID for their documented 48-hour retry window), Meta's Lead
 * Ads webhook + Graph API `field_data` shape, and the WhatsApp Cloud API's
 * `entry[].changes[].value` message structure. JustDial, Google Ads (via
 * Zapier/Make), and plain website forms don't have one fixed shape, so they
 * fall through to a heuristic alias match across common field names.
 */
class LeadPayloadNormalizer
{
    private const ALIASES = [
        'name' => ['name', 'full_name', 'fullname', 'sender_name', 'contact_name', 'customer_name', 'lead_name', 'first_name'],
        'phone' => ['phone', 'phone_number', 'mobile', 'mobile_number', 'contact_number', 'sender_mobile', 'whatsapp_number', 'contact'],
        'email' => ['email', 'email_address', 'sender_email'],
        'company' => ['company', 'company_name', 'organisation', 'organization', 'business_name', 'sender_company'],
        'city' => ['city', 'sender_city'],
        'state' => ['state', 'sender_state'],
        'message' => ['message', 'query', 'comments', 'comment', 'requirement', 'description', 'query_message', 'notes', 'enquiry'],
    ];

    private const ID_ALIASES = ['id', 'submission_id', 'unique_id', 'event_id', 'message_id', 'lead_id', 'leadgen_id'];

    /**
     * $fieldMapping is a tenant-configured override — e.g. {"phone":"phn"}
     * when a website form's field for phone number happens to be named
     * "phn" instead of anything the built-in alias list or a platform's
     * fixed format would recognize. Always wins over the automatic guess,
     * for any platform, since a human who bothered to set it up knows their
     * own form better than a heuristic ever could.
     */
    public function normalize(string $platform, array $data, array $fieldMapping = []): array
    {
        $result = match ($platform) {
            'indiamart' => $this->fromIndiaMart($data),
            'whatsapp' => $this->fromWhatsApp($data),
            'facebook' => $this->fromFacebook($data),
            default => $this->fromGeneric($data),
        };

        return empty($fieldMapping) ? $result : $this->applyMapping($result, $data, $fieldMapping);
    }

    private function applyMapping(array $result, array $data, array $fieldMapping): array
    {
        $lower = [];
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $lower[strtolower((string) $key)] = $value;
            }
        }

        foreach ($fieldMapping as $crmField => $incomingKey) {
            if (! is_string($incomingKey) || $incomingKey === '') {
                continue;
            }

            $value = $lower[strtolower($incomingKey)] ?? null;
            if ($value !== null && $value !== '') {
                $result[$crmField] = $value;
            }
        }

        return $result;
    }

    private function fromIndiaMart(array $data): array
    {
        // IndiaMART sometimes wraps the fields under a "RESPONSE" key
        // depending on how the sending side is configured — check both.
        $d = $data['RESPONSE'] ?? $data;

        return array_filter([
            'name' => $d['SENDER_NAME'] ?? null,
            'phone' => $d['SENDER_MOBILE'] ?? null,
            'email' => $d['SENDER_EMAIL'] ?? null,
            'company' => $d['SENDER_COMPANY'] ?? null,
            'city' => $d['SENDER_CITY'] ?? null,
            'state' => $d['SENDER_STATE'] ?? null,
            'message' => $d['QUERY_MESSAGE'] ?? $d['SUBJECT'] ?? null,
            'product' => $d['QUERY_PRODUCT_NAME'] ?? null,
            'external_ref' => $d['UNIQUE_QUERY_ID'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    private function fromWhatsApp(array $data): array
    {
        $value = $data['entry'][0]['changes'][0]['value'] ?? [];
        $message = $value['messages'][0] ?? null;
        $contact = $value['contacts'][0] ?? null;

        if (! $message) {
            return [];
        }

        return array_filter([
            'name' => $contact['profile']['name'] ?? null,
            'phone' => $message['from'] ?? ($contact['wa_id'] ?? null),
            'message' => $message['text']['body'] ?? ('['.($message['type'] ?? 'message').']'),
            'external_ref' => $message['id'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Native Meta leadgen webhooks only ever carry a `leadgen_id` — the
     * actual answers require a separate Graph API call with a Page access
     * token, which is a whole OAuth+App-Review flow outside what a single
     * webhook receiver can do. If a `field_data` array IS present, it means
     * this arrived via Zapier/Make (which resolves it for you) rather than
     * straight from Meta, so it's parsed the same way Meta's own Graph API
     * response shapes it: a list of {name, values[]} pairs.
     */
    private function fromFacebook(array $data): array
    {
        $fieldData = $data['field_data']
            ?? $data['entry'][0]['changes'][0]['value']['field_data']
            ?? null;

        if (is_array($fieldData)) {
            $flat = [];
            foreach ($fieldData as $field) {
                $key = strtolower((string) ($field['name'] ?? ''));
                $flat[$key] = $field['values'][0] ?? null;
            }

            $normalized = $this->fromGeneric($flat);
            $leadgenId = $data['leadgen_id'] ?? $data['entry'][0]['changes'][0]['value']['leadgen_id'] ?? null;
            if ($leadgenId) {
                $normalized['external_ref'] = (string) $leadgenId;
            }

            return $normalized;
        }

        $leadgenId = $data['entry'][0]['changes'][0]['value']['leadgen_id'] ?? $data['leadgen_id'] ?? null;

        return $leadgenId ? ['external_ref' => (string) $leadgenId] : [];
    }

    private function fromGeneric(array $data): array
    {
        $lower = [];
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $lower[strtolower((string) $key)] = $value;
            }
        }

        $result = [];
        foreach (self::ALIASES as $field => $aliases) {
            foreach ($aliases as $alias) {
                if (isset($lower[$alias]) && $lower[$alias] !== '') {
                    $result[$field] = $lower[$alias];
                    break;
                }
            }
        }

        foreach (self::ID_ALIASES as $alias) {
            if (isset($lower[$alias]) && $lower[$alias] !== '') {
                $result['external_ref'] = (string) $lower[$alias];
                break;
            }
        }

        return $result;
    }
}

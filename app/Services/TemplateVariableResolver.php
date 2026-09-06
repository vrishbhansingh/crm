<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyDetails;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;

/**
 * Fills `{{token.field}}` placeholders in a template's subject/body with
 * real record data. A recipient (Lead/Contact/Company) is resolved into a
 * flat token map — pulling in whatever related deal/company/contact records
 * exist alongside it — plus the tenant's own org profile and the record's
 * owning user, so a single template reads naturally for any audience type.
 */
class TemplateVariableResolver
{
    /**
     * Every token this app understands, grouped by prefix — drives both
     * resolution and the "insert variable" picker in the template editor.
     */
    public static function catalog(): array
    {
        return [
            'lead' => ['name', 'email', 'phone', 'company_name', 'city', 'state', 'country', 'product', 'lead_status', 'priority'],
            'deal' => ['name', 'amount', 'currency', 'status', 'stage', 'expected_close_date'],
            'company' => ['name', 'website', 'industry', 'city', 'phone', 'email'],
            'contact' => ['name', 'email', 'phone', 'designation'],
            'user' => ['name', 'email', 'phone'],
            'organization' => ['name', 'email', 'phone', 'address', 'city'],
        ];
    }

    /**
     * Which token groups are actually meaningful for a given audience type
     * — used by the UI to only offer relevant insert buttons; resolve()
     * itself still fills in whatever related records happen to exist.
     */
    public static function tokensFor(string $audienceType): array
    {
        $catalog = static::catalog();

        // Only the audience's own fields, plus the two that make sense on
        // any template regardless of who it's sent to (the sender and the
        // sending organization) — deliberately not every related record
        // resolve() happens to be able to reach (e.g. a lead's linked deal),
        // which read as "why is everything mixed together" clutter rather
        // than a useful shortcut.
        $groups = match ($audienceType) {
            'leads' => ['lead', 'user', 'organization'],
            'contacts' => ['contact', 'user', 'organization'],
            'companies' => ['company', 'user', 'organization'],
            default => array_keys($catalog),
        };

        $tokens = [];
        foreach ($groups as $group) {
            foreach ($catalog[$group] ?? [] as $field) {
                $tokens[] = "{$group}.{$field}";
            }
        }

        return $tokens;
    }

    /**
     * Build the token => value map for one recipient record.
     */
    public function contextFor(Lead|Contact|Company $recipient, ?Tenant $tenant): array
    {
        $context = $this->organizationContext($tenant);

        if ($recipient instanceof Lead) {
            $context = array_merge($context, $this->leadContext($recipient));
        } elseif ($recipient instanceof Contact) {
            $context = array_merge($context, $this->contactContext($recipient));
        } elseif ($recipient instanceof Company) {
            $context = array_merge($context, $this->companyContext($recipient));
        }

        return $context;
    }

    /**
     * Replace every `{{token}}` in $text using $context; unresolvable or
     * unmapped tokens render as empty rather than leaking raw `{{...}}`
     * markup into a sent email. The editor inserts tokens wrapped in a
     * `.var-token` span for a nicer authoring view — that wrapper is
     * matched and discarded too, so a sent email shows the plain resolved
     * value rather than a leftover pill around real data.
     */
    public function resolve(string $text, array $context): string
    {
        $pattern = '/(?:<span[^>]*class="[^"]*var-token[^"]*"[^>]*>\s*)?\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}(?:\s*<\/span>)?/';

        return preg_replace_callback($pattern, function ($matches) use ($context) {
            $value = $context[$matches[1]] ?? '';

            return $value === null ? '' : (string) $value;
        }, $text);
    }

    private function leadContext(Lead $lead): array
    {
        $context = [
            'lead.name' => $lead->name,
            'lead.email' => $lead->email,
            'lead.phone' => $lead->phone,
            'lead.company_name' => $lead->company_name,
            'lead.city' => $lead->city,
            'lead.state' => $lead->state,
            'lead.country' => $lead->country,
            'lead.product' => $lead->product,
            'lead.lead_status' => $lead->lead_status,
            'lead.priority' => $lead->priority,
        ];

        if ($lead->assignedUser) {
            $context = array_merge($context, $this->userContext($lead->assignedUser));
        }

        if ($lead->company) {
            $context = array_merge($context, $this->companyContext($lead->company));
        }

        if ($lead->contact) {
            $context = array_merge($context, $this->contactContext($lead->contact));
        }

        if ($lead->deal) {
            $context = array_merge($context, $this->dealContext($lead->deal));
        }

        return $context;
    }

    private function contactContext(Contact $contact): array
    {
        $context = [
            'contact.name' => $contact->name,
            'contact.email' => $contact->email,
            'contact.phone' => $contact->phone,
            'contact.designation' => $contact->designation,
        ];

        if ($contact->company) {
            $context = array_merge($context, $this->companyContext($contact->company));
        }

        if ($contact->owner) {
            $context = array_merge($context, $this->userContext($contact->owner));
        }

        return $context;
    }

    private function companyContext(Company $company): array
    {
        $context = [
            'company.name' => $company->name,
            'company.website' => $company->website,
            'company.industry' => $company->industry,
            'company.city' => $company->city,
            'company.phone' => $company->phone,
            'company.email' => $company->email,
        ];

        if ($company->owner) {
            $context = array_merge($context, $this->userContext($company->owner));
        }

        return $context;
    }

    private function dealContext(Deal $deal): array
    {
        return [
            'deal.name' => $deal->name,
            'deal.amount' => $deal->amount,
            'deal.currency' => $deal->currency,
            'deal.status' => $deal->status,
            'deal.stage' => $deal->stage?->name,
            'deal.expected_close_date' => optional($deal->expected_close_date)->format('d M Y'),
        ];
    }

    private function userContext(User $user): array
    {
        return [
            'user.name' => $user->name,
            'user.email' => $user->email,
            'user.phone' => $user->phone,
        ];
    }

    private function organizationContext(?Tenant $tenant): array
    {
        if (! $tenant) {
            return [];
        }

        $details = CompanyDetails::first();

        return [
            'organization.name' => $details->company_name ?? $tenant->name,
            'organization.email' => $details->email ?? $tenant->contact_email,
            'organization.phone' => $details->phone ?? null,
            'organization.address' => $details->address ?? null,
            'organization.city' => $details->city ?? null,
        ];
    }
}

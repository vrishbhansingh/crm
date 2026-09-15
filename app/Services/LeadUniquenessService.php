<?php

namespace App\Services;

use App\Models\Lead;

/**
 * Enforces the "a Lead's email/phone can only be used once" business rule
 * across every creation path (manual form, edit form, the public API, the
 * webhook ingestion pipeline, and bulk Excel import) from one place, so the
 * normalization rules and the duplicate query itself can't drift between
 * them.
 */
class LeadUniquenessService
{
    /**
     * Lowercased and trimmed, so "John@Example.com" and " john@example.com "
     * are recognized as the same address.
     */
    public static function normalizeEmail(?string $email): ?string
    {
        $email = trim((string) $email);

        return $email === '' ? null : mb_strtolower($email);
    }

    /**
     * Digits only, with a leading Indian country code or trunk "0" prefix
     * stripped — "+91 98765-43210", "91 9876543210", "09876543210", and
     * "9876543210" all normalize to the same 10-digit value.
     */
    public static function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return $digits;
    }

    /**
     * The existing Lead (if any) already using this email or phone, scoped
     * to whichever tenant connection $excludeId/the query runs against
     * (Lead is a per-tenant table, so no explicit tenant_id filter is
     * needed here — BelongsToTenant's global scope already applies).
     */
    public static function findDuplicate(?string $emailNormalized, ?string $phoneNormalized, ?int $excludeId = null): ?Lead
    {
        if (! $emailNormalized && ! $phoneNormalized) {
            return null;
        }

        return Lead::query()
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($emailNormalized, $phoneNormalized) {
                if ($emailNormalized) {
                    $q->orWhere('email_normalized', $emailNormalized);
                }
                if ($phoneNormalized) {
                    $q->orWhere('phone_normalized', $phoneNormalized);
                }
            })
            ->first();
    }

    /**
     * Which of email/phone the given duplicate actually collides on — used
     * to word the rejection message precisely ("email" vs "phone number").
     */
    public static function duplicateField(Lead $duplicate, ?string $emailNormalized, ?string $phoneNormalized): string
    {
        if ($emailNormalized && $duplicate->email_normalized === $emailNormalized) {
            return 'email';
        }

        return 'phone';
    }
}

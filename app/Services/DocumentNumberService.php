<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Generalizes QuotationNumberService/LeadNumberService's race-safe
 * tenant_sequences upsert so every other numbered document (PO, RFQ, GRN,
 * Invoice, Credit/Debit Note) doesn't need its own near-identical service
 * class. One `tenant_sequences` row per $sequenceName; $sharedModeCount is
 * only invoked for the shared/test tenancy mode fallback.
 */
class DocumentNumberService
{
    public static function assign(Model $document, string $numberField, string $sequenceName, string $prefix, callable $sharedModeCount): Model
    {
        abort_if($document->tenant_id === null, 422, 'A tenant is required for numbering.');

        $connection = $document->getConnectionName();

        return DB::connection($connection)->transaction(function () use ($document, $connection, $numberField, $sequenceName, $prefix, $sharedModeCount) {
            if (config('tenancy.mode') === 'database') {
                DB::connection($connection)->statement(
                    'INSERT INTO tenant_sequences (name, current_value) VALUES (?, LAST_INSERT_ID(1)) ON DUPLICATE KEY UPDATE current_value = LAST_INSERT_ID(current_value + 1)',
                    [$sequenceName]
                );
                $sequence = (int) DB::connection($connection)->getPdo()->lastInsertId();
            } else {
                Tenant::whereKey($document->tenant_id)->lockForUpdate()->firstOrFail();
                $sequence = ((int) $sharedModeCount()) + 1;
            }

            $document->{$numberField} = $prefix.'-'.now()->format('Y').'-'.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
            $document->save();

            return $document;
        });
    }
}

<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * Mirrors LeadNumberService's race-safe tenant_sequences upsert. All
 * versions of one quote share a single number (see Quotation::newVersion),
 * so this only runs when creating version 1 of a quote.
 */
class QuotationNumberService
{
    public static function saveNew(Quotation $quotation): Quotation
    {
        abort_if($quotation->tenant_id === null, 422, 'A tenant is required for quotation numbering.');

        $connection = $quotation->getConnectionName();

        return DB::connection($connection)->transaction(function () use ($quotation, $connection) {
            if (config('tenancy.mode') === 'database') {
                DB::connection($connection)->statement(
                    "INSERT INTO tenant_sequences (name, current_value) VALUES ('quotation_number', LAST_INSERT_ID(1)) ON DUPLICATE KEY UPDATE current_value = LAST_INSERT_ID(current_value + 1)"
                );
                $sequence = (int) DB::connection($connection)->getPdo()->lastInsertId();
            } else {
                Tenant::whereKey($quotation->tenant_id)->lockForUpdate()->firstOrFail();
                $sequence = ((int) Quotation::withoutGlobalScopes()
                    ->where('tenant_id', $quotation->tenant_id)
                    ->whereNull('root_quotation_id')
                    ->count()) + 1;
            }

            $quotation->quotation_number = 'QT-'.now()->format('Y').'-'.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
            $quotation->save();

            return $quotation;
        });
    }
}

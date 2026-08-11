<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class LeadNumberService
{
    public static function saveNew(Lead $lead): Lead
    {
        abort_if($lead->tenant_id === null, 422, 'A tenant is required for lead numbering.');

        $connection = $lead->getConnectionName();

        return DB::connection($connection)->transaction(function () use ($lead, $connection) {
            if (config('tenancy.mode') === 'database') {
                DB::connection($connection)->statement(
                    "INSERT INTO tenant_sequences (name, current_value) VALUES ('lead_number', LAST_INSERT_ID(1)) ON DUPLICATE KEY UPDATE current_value = LAST_INSERT_ID(current_value + 1)"
                );
                $lead->lead_number = (int) DB::connection($connection)->getPdo()->lastInsertId();
                $lead->save();

                return $lead;
            }

            Tenant::whereKey($lead->tenant_id)->lockForUpdate()->firstOrFail();
            $lead->lead_number = ((int) Lead::withoutGlobalScopes()
                ->where('tenant_id', $lead->tenant_id)
                ->max('lead_number')) + 1;
            $lead->save();

            return $lead;
        });
    }
}

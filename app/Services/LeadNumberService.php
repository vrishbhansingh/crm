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

        return DB::transaction(function () use ($lead) {
            Tenant::whereKey($lead->tenant_id)->lockForUpdate()->firstOrFail();
            $lead->lead_number = ((int) Lead::withoutGlobalScopes()
                ->where('tenant_id', $lead->tenant_id)
                ->max('lead_number')) + 1;
            $lead->save();

            return $lead;
        });
    }
}

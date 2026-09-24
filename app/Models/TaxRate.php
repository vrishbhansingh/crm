<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'rate_percent',
        'is_active',
    ];

    protected $casts = [
        'rate_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Standard India GST slabs, seeded for every tenant (fresh provisioning
     * and backfilled for existing ones via crm:sync-master-data) so the
     * tax-rate dropdown isn't empty out of the box. Tenants can still add,
     * rename, or deactivate their own on top of these.
     */
    public static function defaultRatePercents(): array
    {
        return [0, 5, 12, 18, 28];
    }

    public static function labelFor(float $ratePercent): string
    {
        return 'GST '.rtrim(rtrim(number_format($ratePercent, 2), '0'), '.').'%';
    }
}

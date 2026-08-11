<?php

namespace App\Models;

use App\Support\TenantContext;
use App\Models\Concerns\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class MasterValue extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'master_type_id',
        'tenant_id',
        'code',
        'label',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function masterType(): BelongsTo
    {
        return $this->belongsTo(MasterType::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Active values for a master type, visible to the current tenant context:
     * global defaults (tenant_id null) plus that tenant's own additions.
     * A null tenant context (platform Super Admin / console) sees globals only.
     */
    public static function options(string $typeCode): Collection
    {
        $type = MasterType::where('code', $typeCode)->where('is_active', true)->first();

        if (! $type) {
            return collect();
        }

        $tenantId = TenantContext::id();

        return static::where('master_type_id', $type->id)
            ->where('is_active', true)
            ->where(function ($query) use ($tenantId) {
                $query->whereNull('tenant_id');

                if ($tenantId !== null) {
                    $query->orWhere('tenant_id', $tenantId);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get(['id', 'code', 'label', 'color']);
    }
}

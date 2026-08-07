<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pipeline extends Model
{
    use HasFactory, BelongsToTenant;
    protected $table = 'pipelines';
    protected $fillable = [
        'tenant_id',
        'name',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class)->orderBy('sort_order');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * Lazily provisions a tenant's first pipeline the moment they touch the
     * Deals module — no seeding step at tenant-creation time needed.
     */
    public static function ensureDefaultForTenant(?int $tenantId): Pipeline
    {
        $existing = static::where('tenant_id', $tenantId)->orderBy('sort_order')->first();

        if ($existing) {
            return $existing;
        }

        $pipeline = static::create([
            'tenant_id' => $tenantId,
            'name' => 'Sales Pipeline',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $stages = [
            ['name' => 'Prospecting', 'sort_order' => 0, 'is_won' => false, 'is_lost' => false],
            ['name' => 'Proposal', 'sort_order' => 1, 'is_won' => false, 'is_lost' => false],
            ['name' => 'Negotiation', 'sort_order' => 2, 'is_won' => false, 'is_lost' => false],
            ['name' => 'Won', 'sort_order' => 3, 'is_won' => true, 'is_lost' => false],
            ['name' => 'Lost', 'sort_order' => 4, 'is_won' => false, 'is_lost' => true],
        ];

        foreach ($stages as $stage) {
            $pipeline->stages()->create(array_merge($stage, ['tenant_id' => $tenantId]));
        }

        return $pipeline;
    }
}

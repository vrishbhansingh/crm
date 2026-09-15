<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class DealAssignmentRule extends Model
{
    use BelongsToTenant;

    public const TYPES = ['pipeline', 'source', 'round_robin'];

    protected $fillable = [
        'tenant_id', 'rule_type', 'match_value', 'agent_ids', 'cursor',
        'is_active', 'sort_order', 'created_by',
    ];

    protected $casts = [
        'agent_ids' => 'array',
        'is_active' => 'boolean',
    ];

    public function activeAgents()
    {
        $ids = collect($this->agent_ids ?? [])->filter()->values();
        $users = User::whereIn('id', $ids)->where('status', 'Active')->get()->keyBy('id');

        return $ids->map(fn ($id) => $users->get($id))->filter()->values();
    }
}

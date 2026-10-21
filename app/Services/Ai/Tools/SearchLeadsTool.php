<?php

namespace App\Services\Ai\Tools;

use App\Models\User;

class SearchLeadsTool extends CrmTool
{
    public function name(): string
    {
        return 'search_leads';
    }

    public function description(): string
    {
        return 'Find leads by free text (name, company, phone, email, product), status, source, priority, or follow-up state. Returns at most 25 leads, newest first. Agents only ever see their own assigned leads.';
    }

    public function permission(): string
    {
        return 'leads.view';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string', 'description' => 'Text to match against name, company, phone, email or product.'],
                'status' => ['type' => 'string', 'description' => 'Lead status code, e.g. new, contacted, interested, follow_up, not_interested, converted, closed.'],
                'source' => ['type' => 'string', 'description' => 'Lead source code, e.g. website, indiamart, facebook_ads.'],
                'priority' => ['type' => 'string', 'description' => 'low, medium or high.'],
                'follow_up' => ['type' => 'string', 'enum' => ['overdue', 'today', 'none'], 'description' => 'Only leads whose follow-up is overdue, due today, or not scheduled.'],
                'assigned_to_name' => ['type' => 'string', 'description' => 'Team member name. Honoured only for admins/managers.'],
                'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-25. Default 10.'],
            ],
        ];
    }

    public function run(User $user, array $input): array
    {
        $query = $this->leads($user);

        if (! empty($input['query'])) {
            $term = '%'.addcslashes((string) $input['query'], '%_\\').'%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('company_name', 'like', $term)
                ->orWhere('phone', 'like', $term)->orWhere('email', 'like', $term)->orWhere('product', 'like', $term));
        }
        foreach (['status' => 'lead_status', 'source' => 'lead_source', 'priority' => 'priority'] as $key => $column) {
            if (! empty($input[$key])) {
                $query->where($column, (string) $input[$key]);
            }
        }

        $today = now(optional($user->tenant)->timezone ?: config('app.timezone'))->toDateString();
        match ($input['follow_up'] ?? null) {
            'overdue' => $query->whereNotNull('follow_up_date')->where('follow_up_date', '<', $today)->whereNotIn('lead_status', ['converted', 'closed']),
            'today' => $query->where('follow_up_date', $today),
            'none' => $query->whereNull('follow_up_date'),
            default => null,
        };

        if (! empty($input['assigned_to_name']) && $user->hasElevatedAccess()) {
            $ids = User::where('tenant_id', $user->tenant_id)->where('name', 'like', '%'.addcslashes((string) $input['assigned_to_name'], '%_\\').'%')->pluck('id');
            $query->whereIn('assigned_to', $ids);
        }

        $total = (clone $query)->count();
        $rows = $query->with('assignedUser:id,name')->latest('id')->limit($this->limit($input))->get();

        return [
            'total_matching' => $total,
            'returned' => $rows->count(),
            'leads' => $rows->map(fn ($l) => [
                'id' => $l->id,
                'lead_number' => $l->lead_number,
                'name' => $l->name,
                'company' => $l->company_name,
                'phone' => $l->phone,
                'email' => $l->email,
                'status' => $l->lead_status,
                'source' => $l->lead_source,
                'priority' => $l->priority,
                'product' => $l->product,
                'assigned_to' => $l->assignedUser?->name,
                'follow_up_date' => $l->follow_up_date,
                'converted' => $l->is_converted === 'Yes',
                'created' => optional($l->created_at)->toDateString(),
            ])->all(),
        ];
    }
}

<?php

namespace App\Services\Ai\Tools;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;

/**
 * One capability the assistant may use. The model never touches the
 * database: it can only ask for one of these, and each one re-checks the
 * user's permission and applies the same data scoping the CRM pages use
 * (admins/managers see the whole company, everyone else only their own
 * records). That is the real security boundary — the prompt is not.
 */
abstract class CrmTool
{
    abstract public function name(): string;

    abstract public function description(): string;

    /** JSON schema for the tool's input. */
    abstract public function schema(): array;

    /** Spatie permission the user must hold, e.g. 'leads.view'. */
    abstract public function permission(): string;

    /** True when only elevated roles (Admin/Manager/Sales Manager) may use it. */
    public function elevatedOnly(): bool
    {
        return false;
    }

    abstract public function run(User $user, array $input): array;

    public function allowedFor(User $user): bool
    {
        if ($this->elevatedOnly() && ! $user->hasElevatedAccess()) {
            return false;
        }

        return $user->can($this->permission());
    }

    public function definition(): array
    {
        return ['name' => $this->name(), 'description' => $this->description(), 'inputSchema' => $this->schema()];
    }

    protected function leads(User $user)
    {
        return $user->hasElevatedAccess() ? Lead::query() : Lead::where('assigned_to', $user->id);
    }

    protected function deals(User $user)
    {
        return $user->hasElevatedAccess() ? Deal::query() : Deal::where('owner_id', $user->id);
    }

    protected function tasks(User $user)
    {
        return $user->hasElevatedAccess()
            ? Task::query()
            : Task::where(fn ($q) => $q->where('assigned_to', $user->id)->orWhere('created_by', $user->id));
    }

    protected function limit(array $input, int $default = 10, int $max = 25): int
    {
        return max(1, min($max, (int) ($input['limit'] ?? $default)));
    }
}

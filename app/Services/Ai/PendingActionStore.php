<?php

namespace App\Services\Ai;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Holds actions the assistant has PROPOSED but the user has not yet
 * confirmed. The model never executes a write: it can only park one here,
 * and it only runs when the user presses Confirm (a separate HTTP request
 * the model cannot make). Entries are bound to the proposing user, expire
 * after ten minutes, and are consumed on first use.
 */
class PendingActionStore
{
    private const KEY = 'ai_pending_actions';
    private const TTL_SECONDS = 600;

    /** @var array<int, array{id: string, summary: string}> */
    private array $created = [];

    public function put(User $user, string $tool, array $params, string $summary): string
    {
        $all = $this->all();
        $id = Str::random(24);
        $all[$id] = ['user_id' => $user->id, 'tool' => $tool, 'params' => $params, 'summary' => $summary, 'expires' => now()->timestamp + self::TTL_SECONDS];
        session()->put(self::KEY, $this->unexpired($all));
        $this->created[] = ['id' => $id, 'summary' => $summary];

        return $id;
    }

    public function get(User $user, string $id): ?array
    {
        $entry = $this->unexpired($this->all())[$id] ?? null;

        return $entry && (int) $entry['user_id'] === (int) $user->id ? $entry : null;
    }

    public function forget(string $id): void
    {
        $all = $this->all();
        unset($all[$id]);
        session()->put(self::KEY, $all);
    }

    /** Proposals made since the last call — shown to the user as Confirm/Cancel cards. */
    public function pullCreated(): array
    {
        $created = $this->created;
        $this->created = [];

        return $created;
    }

    private function all(): array
    {
        return (array) session()->get(self::KEY, []);
    }

    private function unexpired(array $all): array
    {
        return array_filter($all, fn ($e) => ($e['expires'] ?? 0) >= now()->timestamp);
    }
}

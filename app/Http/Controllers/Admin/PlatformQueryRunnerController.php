<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use App\Models\Tenant;
use App\Services\PlatformAuditLogger;
use App\Tenancy\TenantConnectionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * A phpMyAdmin replacement for the Super Admin: run arbitrary SQL against
 * the master database or any single tenant's database, without leaving the
 * app. Every attempt — successful or not — is written to the platform
 * audit log (query text, target, outcome, timing), and anything that isn't
 * a plain read requires the caller to have already confirmed it once
 * (the UI does this via a confirm dialog; this endpoint re-checks it
 * server-side rather than trusting the client).
 */
class PlatformQueryRunnerController extends Controller
{
    private const READ_VERBS = ['SELECT', 'SHOW', 'DESCRIBE', 'DESC', 'EXPLAIN'];

    private const DISPLAY_ROW_LIMIT = 1000;

    public function index()
    {
        $tenants = Tenant::orderBy('name')->get(['id', 'name']);
        $history = PlatformAuditLog::where('event', 'query_runner.executed')
            ->with('actor:id,name')
            ->latest('id')
            ->limit(25)
            ->get();

        return view('platform.query-runner', compact('tenants', 'history'));
    }

    public function run(Request $request, TenantConnectionManager $connections, PlatformAuditLogger $logger)
    {
        $data = $request->validate([
            'connection' => ['required', 'string'],
            'sql' => ['required', 'string'],
            'confirm' => ['nullable', 'boolean'],
        ]);

        $sql = trim($data['sql']);
        // Only ever run a single statement — see class docblock. This also
        // quietly forecloses the classic "SELECT 1; DROP TABLE x" trick.
        $sql = rtrim(rtrim($sql), ';');
        if (str_contains($sql, ';')) {
            return response()->json(['success' => false, 'error' => 'Only one statement at a time — remove the extra ";".']);
        }

        $firstWord = strtoupper(strtok($sql, " \t\n\r(") ?: '');
        $isRead = in_array($firstWord, self::READ_VERBS, true);

        if (! $isRead && ! ($data['confirm'] ?? false)) {
            return response()->json(['success' => false, 'needs_confirmation' => true]);
        }

        $isMaster = $data['connection'] === 'master';
        $tenant = null;

        if (! $isMaster) {
            $tenant = Tenant::find((int) $data['connection']);
            if (! $tenant) {
                return response()->json(['success' => false, 'error' => 'Unknown company.']);
            }
        }

        // "shared" tenancy mode keeps every tenant's data in the one master
        // database (no separate per-tenant schema exists to switch to) —
        // used in tests, and optionally for real deployments that opt into
        // it. Picking a company there still just queries the shared DB.
        $needsTenantSwitch = ! $isMaster && config('tenancy.mode') !== 'shared';
        $connectionName = $needsTenantSwitch ? config('tenancy.tenant_connection', 'tenant') : config('tenancy.master_connection', 'mysql');

        try {
            if ($needsTenantSwitch) {
                $connections->activate($tenant);
            }

            $started = microtime(true);
            $result = $isRead ? $this->runRead($connectionName, $sql) : $this->runWrite($connectionName, $sql);
            $durationMs = (int) round((microtime(true) - $started) * 1000);

            $logger->record('query_runner.executed', $tenant, null, [
                'connection' => $isMaster ? 'master' : "tenant #{$tenant->id}",
                'sql' => Str::limit($sql, 500),
                'type' => $isRead ? 'read' : 'write',
                'success' => true,
                'duration_ms' => $durationMs,
            ]);

            return response()->json(array_merge(['success' => true, 'duration_ms' => $durationMs], $result));
        } catch (Throwable $exception) {
            $logger->record('query_runner.executed', $tenant, null, [
                'connection' => $isMaster ? 'master' : "tenant #{$tenant?->id}",
                'sql' => Str::limit($sql, 500),
                'type' => $isRead ? 'read' : 'write',
                'success' => false,
                'error' => $exception->getMessage(),
            ]);

            return response()->json(['success' => false, 'error' => $exception->getMessage()]);
        } finally {
            if ($needsTenantSwitch) {
                $connections->deactivate();
            }
        }
    }

    public function tables(Request $request, TenantConnectionManager $connections)
    {
        $data = $request->validate(['connection' => ['required', 'string']]);
        $isMaster = $data['connection'] === 'master';
        $needsTenantSwitch = ! $isMaster && config('tenancy.mode') !== 'shared';
        $connectionName = $needsTenantSwitch ? config('tenancy.tenant_connection', 'tenant') : config('tenancy.master_connection', 'mysql');

        try {
            if ($needsTenantSwitch) {
                $tenant = Tenant::findOrFail((int) $data['connection']);
                $connections->activate($tenant);
            }

            $rows = DB::connection($connectionName)->select('SHOW TABLES');
            $tables = array_map(fn ($row) => array_values((array) $row)[0], $rows);
            sort($tables);

            return response()->json(['success' => true, 'tables' => $tables]);
        } catch (Throwable $exception) {
            return response()->json(['success' => false, 'error' => $exception->getMessage()]);
        } finally {
            if ($needsTenantSwitch) {
                $connections->deactivate();
            }
        }
    }

    private function runRead(string $connectionName, string $sql): array
    {
        $rows = DB::connection($connectionName)->select($sql);
        $total = count($rows);
        $truncated = $total > self::DISPLAY_ROW_LIMIT;
        $rows = array_slice($rows, 0, self::DISPLAY_ROW_LIMIT);
        $columns = $rows === [] ? [] : array_keys((array) $rows[0]);

        return [
            'type' => 'read',
            'columns' => $columns,
            'rows' => array_map(fn ($row) => array_values((array) $row), $rows),
            'total' => $total,
            'truncated' => $truncated,
        ];
    }

    private function runWrite(string $connectionName, string $sql): array
    {
        $pdo = DB::connection($connectionName)->getPdo();
        $affected = $pdo->exec($sql);

        if ($affected === false) {
            $error = $pdo->errorInfo();
            throw new \RuntimeException($error[2] ?? 'The statement failed.');
        }

        return ['type' => 'write', 'affected' => $affected];
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\PlatformAuditLogger;
use App\Tenancy\TenantConnectionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * A phpMyAdmin replacement for the Super Admin: run SQL against the master
 * database, one tenant's database, or every ready tenant's database at
 * once, without leaving the app. Every attempt — successful or not — is
 * written to the platform audit log. The Allow-write/Allow-DDL checkboxes
 * are enforced server-side by classifying the statement's leading
 * keyword, not just trusted from the client.
 */
class PlatformQueryRunnerController extends Controller
{
    private const READ_KEYWORDS = ['SELECT', 'SHOW', 'DESCRIBE', 'DESC', 'EXPLAIN', 'WITH'];

    private const WRITE_KEYWORDS = ['INSERT', 'UPDATE', 'DELETE', 'REPLACE'];

    private const DDL_KEYWORDS = ['ALTER', 'CREATE', 'DROP', 'TRUNCATE', 'RENAME'];

    public function index()
    {
        $tenants = Tenant::where('provision_status', 'ready')->orderBy('name')->get(['id', 'name', 'database_name']);

        return view('platform.query-runner', [
            'tenants' => $tenants,
            'masterDatabase' => config('database.connections.'.config('tenancy.master_connection', 'mysql').'.database'),
        ]);
    }

    public function tables(Request $request, TenantConnectionManager $connections)
    {
        $data = $request->validate(['connection' => ['required', 'string']]);
        $isMaster = $data['connection'] === 'master';
        $needsTenantSwitch = ! $isMaster && config('tenancy.mode') !== 'shared';
        $connectionName = $needsTenantSwitch ? config('tenancy.tenant_connection', 'tenant') : config('tenancy.master_connection', 'mysql');

        try {
            if ($needsTenantSwitch) {
                $connections->activate((int) $data['connection']);
            }

            $rows = DB::connection($connectionName)->select('SHOW TABLES');
            $tables = array_map(fn ($row) => array_values((array) $row)[0], $rows);
            sort($tables);

            return response()->json(['status' => true, 'tables' => $tables]);
        } catch (Throwable $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()], 422);
        } finally {
            if ($needsTenantSwitch) {
                $connections->deactivate();
            }
        }
    }

    public function run(Request $request, TenantConnectionManager $connections, PlatformAuditLogger $audit)
    {
        $data = $request->validate([
            'sql' => ['required', 'string', 'max:20000'],
            'mode' => ['required', Rule::in(['single', 'all'])],
            'tenant_id' => ['nullable', 'integer'],
            'allow_write' => ['nullable', 'boolean'],
            'allow_ddl' => ['nullable', 'boolean'],
            'row_limit' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ]);

        $allowWrite = (bool) ($data['allow_write'] ?? false);
        $allowDdl = (bool) ($data['allow_ddl'] ?? false);
        $rowLimit = $data['row_limit'] ?? 1000;

        [$statement, $kind] = $this->classify($data['sql']);

        if ($kind === 'write' && ! $allowWrite) {
            return response()->json(['status' => false, 'message' => 'This looks like a write statement (INSERT/UPDATE/DELETE) — check "Allow write" to run it.'], 422);
        }
        if ($kind === 'ddl' && ! $allowDdl) {
            return response()->json(['status' => false, 'message' => 'This looks like a DDL statement (ALTER/CREATE/DROP/TRUNCATE/RENAME) — check "Allow DDL" to run it.'], 422);
        }
        if ($kind === 'unknown') {
            return response()->json(['status' => false, 'message' => 'Could not classify this as a safe read/write/DDL statement.'], 422);
        }

        $tenant = $data['mode'] === 'single' && ! empty($data['tenant_id']) ? Tenant::find($data['tenant_id']) : null;
        $targetLabel = $data['mode'] === 'all' ? 'ALL_TENANTS' : ($tenant->name ?? 'master');
        $started = microtime(true);

        try {
            $result = $data['mode'] === 'all'
                ? $this->runAgainstAllTenants($connections, $statement, $kind, $rowLimit)
                : $this->runSingle($connections, $tenant, $statement, $kind, $rowLimit);
        } catch (Throwable $exception) {
            $audit->record('query_runner.executed', $tenant, null, [
                'sql' => Str::limit($statement, 2000), 'mode' => $data['mode'], 'target' => $targetLabel,
                'allow_write' => $allowWrite, 'allow_ddl' => $allowDdl, 'success' => false, 'error' => $exception->getMessage(),
            ]);

            return response()->json(['status' => false, 'message' => $exception->getMessage()], 422);
        }

        $durationMs = (int) round((microtime(true) - $started) * 1000);

        $audit->record('query_runner.executed', $tenant, null, [
            'sql' => Str::limit($statement, 2000), 'mode' => $data['mode'], 'target' => $targetLabel,
            'allow_write' => $allowWrite, 'allow_ddl' => $allowDdl, 'success' => true,
            'row_count' => $result['row_count'], 'duration_ms' => $durationMs,
        ]);

        return response()->json(array_merge(['status' => true, 'duration_ms' => $durationMs], $result));
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function classify(string $sql): array
    {
        $segments = array_values(array_filter(array_map('trim', explode(';', trim($sql))), fn ($segment) => $segment !== ''));
        abort_if(count($segments) !== 1, 422, 'Only a single SQL statement is allowed per run.');

        $statement = $segments[0];
        $withoutLeadingComments = preg_replace('~^(\s*(--[^\n]*\n|\#[^\n]*\n|/\*.*?\*/)\s*)*~s', '', $statement);
        preg_match('/^([A-Za-z]+)/', trim($withoutLeadingComments), $matches);
        $keyword = strtoupper($matches[1] ?? '');

        return match (true) {
            in_array($keyword, self::READ_KEYWORDS, true) => [$statement, 'read'],
            in_array($keyword, self::WRITE_KEYWORDS, true) => [$statement, 'write'],
            in_array($keyword, self::DDL_KEYWORDS, true) => [$statement, 'ddl'],
            default => [$statement, 'unknown'],
        };
    }

    /**
     * Resolves the connection to run against exactly like every other
     * tenant-switching code path in this app: activate() the shared
     * `tenant` connection for a real per-tenant database, or fall straight
     * through to the master connection for "master" / the shared-tenancy
     * test mode (where every tenant already lives in the one database).
     */
    private function connectionFor(TenantConnectionManager $connections, ?Tenant $tenant): string
    {
        if (! $tenant || config('tenancy.mode') === 'shared') {
            return config('tenancy.master_connection', 'mysql');
        }

        $connections->activate($tenant);

        return config('tenancy.tenant_connection', 'tenant');
    }

    private function runSingle(TenantConnectionManager $connections, ?Tenant $tenant, string $statement, string $kind, int $rowLimit): array
    {
        $needsDeactivate = $tenant && config('tenancy.mode') !== 'shared';

        try {
            $connectionName = $this->connectionFor($connections, $tenant);

            return $this->execute(DB::connection($connectionName), $statement, $kind, $rowLimit);
        } finally {
            if ($needsDeactivate) {
                $connections->deactivate();
            }
        }
    }

    private function runAgainstAllTenants(TenantConnectionManager $connections, string $statement, string $kind, int $rowLimit): array
    {
        $tenants = Tenant::where('provision_status', 'ready')->orderBy('name')->get(['id', 'name', 'database_name']);
        $rows = [];

        foreach ($tenants as $tenant) {
            if ($kind === 'read' && count($rows) >= $rowLimit) {
                break;
            }

            $needsDeactivate = config('tenancy.mode') !== 'shared';

            try {
                $connectionName = $this->connectionFor($connections, $tenant);
                $tagged = $this->execute(DB::connection($connectionName), $statement, $kind, $rowLimit - count($rows), ['tenant_id' => $tenant->id, 'tenant_name' => $tenant->name]);
                $rows = array_merge($rows, $tagged['rows']);
            } catch (Throwable $exception) {
                $rows[] = ['tenant_id' => $tenant->id, 'tenant_name' => $tenant->name, 'error' => $exception->getMessage()];
            } finally {
                if ($needsDeactivate) {
                    $connections->deactivate();
                }
            }
        }

        return [
            'columns' => $rows ? array_keys($rows[0]) : ['tenant_id', 'tenant_name'],
            'rows' => $rows,
            'row_count' => count($rows),
            'truncated' => $kind === 'read' && count($rows) >= $rowLimit,
        ];
    }

    private function execute($connection, string $statement, string $kind, int $rowLimit, array $prefixColumns = []): array
    {
        if ($kind === 'read') {
            $rows = collect($connection->select($this->withLimit($statement, $rowLimit)))
                ->map(fn ($row) => array_merge($prefixColumns, (array) $row));

            return [
                'columns' => $rows->isNotEmpty() ? array_keys($rows->first()) : array_keys($prefixColumns),
                'rows' => $rows->values()->all(),
                'row_count' => $rows->count(),
                'truncated' => $rows->count() >= $rowLimit,
            ];
        }

        if ($kind === 'write') {
            $affected = $connection->affectingStatement($statement);

            return ['columns' => array_keys(array_merge($prefixColumns, ['affected_rows' => null])), 'rows' => [array_merge($prefixColumns, ['affected_rows' => $affected])], 'row_count' => $affected, 'truncated' => false];
        }

        $connection->statement($statement);

        return ['columns' => array_keys(array_merge($prefixColumns, ['result' => null])), 'rows' => [array_merge($prefixColumns, ['result' => 'Statement executed successfully.'])], 'row_count' => 0, 'truncated' => false];
    }

    private function withLimit(string $sql, int $rowLimit): string
    {
        if (preg_match('/\bLIMIT\s+\d+/i', $sql)) {
            return $sql;
        }

        return rtrim($sql, "; \t\n\r").' LIMIT '.max(1, $rowLimit);
    }
}

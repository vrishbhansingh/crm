<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\PlatformAuditLogger;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * The only raw-SQL execution surface in the platform — every safety
 * decision here treats the Allow-write/Allow-DDL checkboxes as real
 * server-side gates, not UI decoration, since a super admin's browser
 * click is the only thing standing between this and every tenant's data.
 */
class PlatformQueryRunnerController extends Controller
{
    private const CONNECTION = 'query_runner';

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

    public function run(Request $request, PlatformAuditLogger $audit)
    {
        $data = $request->validate([
            'sql' => ['required', 'string', 'max:20000'],
            'mode' => ['required', Rule::in(['single', 'all'])],
            'tenant_id' => ['nullable', 'integer', Rule::exists('tenants', 'id')],
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
        $targetLabel = $data['mode'] === 'all' ? 'ALL_TENANTS' : ($tenant->database_name ?? 'master');
        $started = microtime(true);

        try {
            $result = $data['mode'] === 'all'
                ? $this->runAgainstAllTenants($statement, $kind, $rowLimit)
                : $this->runSingle($tenant?->database_name ?? config('database.connections.'.config('tenancy.master_connection', 'mysql').'.database'), $statement, $kind, $rowLimit);
        } catch (Throwable $exception) {
            $audit->record('query_runner.execute', $tenant, null, [
                'sql' => $statement, 'mode' => $data['mode'], 'target_database' => $targetLabel,
                'allow_write' => $allowWrite, 'allow_ddl' => $allowDdl, 'error' => $exception->getMessage(),
            ]);

            return response()->json(['status' => false, 'message' => $exception->getMessage()], 422);
        } finally {
            DB::purge(self::CONNECTION);
        }

        $durationMs = (int) round((microtime(true) - $started) * 1000);

        $audit->record('query_runner.execute', $tenant, null, [
            'sql' => $statement, 'mode' => $data['mode'], 'target_database' => $targetLabel,
            'allow_write' => $allowWrite, 'allow_ddl' => $allowDdl, 'row_count' => $result['row_count'], 'duration_ms' => $durationMs,
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

    private function connectTo(string $databaseName): Connection
    {
        $base = config('database.connections.'.config('tenancy.master_connection', 'mysql'));
        config(['database.connections.'.self::CONNECTION => array_merge($base, ['database' => $databaseName])]);
        DB::purge(self::CONNECTION);

        $connection = DB::connection(self::CONNECTION);

        try {
            $connection->statement('SET SESSION MAX_EXECUTION_TIME = 10000');
        } catch (Throwable) {
            // Best-effort — older MySQL/MariaDB builds don't support this
            // session variable; a runaway query is still bounded by the
            // row limit and the request's own PHP execution timeout.
        }

        return $connection;
    }

    private function runSingle(string $databaseName, string $statement, string $kind, int $rowLimit): array
    {
        $connection = $this->connectTo($databaseName);

        if ($kind === 'read') {
            return $this->runRead($connection, $statement, $rowLimit);
        }

        if ($kind === 'write') {
            $affected = $connection->affectingStatement($statement);

            return ['columns' => ['affected_rows'], 'rows' => [['affected_rows' => $affected]], 'row_count' => $affected, 'truncated' => false];
        }

        $connection->statement($statement);

        return ['columns' => ['result'], 'rows' => [['result' => 'Statement executed successfully.']], 'row_count' => 0, 'truncated' => false];
    }

    private function runRead(Connection $connection, string $statement, int $rowLimit): array
    {
        $rows = collect($connection->select($this->withLimit($statement, $rowLimit)))->map(fn ($row) => (array) $row);

        return [
            'columns' => $rows->isNotEmpty() ? array_keys($rows->first()) : [],
            'rows' => $rows->values()->all(),
            'row_count' => $rows->count(),
            'truncated' => $rows->count() >= $rowLimit,
        ];
    }

    private function runAgainstAllTenants(string $statement, string $kind, int $rowLimit): array
    {
        $tenants = Tenant::where('provision_status', 'ready')->orderBy('name')->get(['id', 'name', 'database_name']);
        $rows = [];

        foreach ($tenants as $tenant) {
            if ($kind === 'read' && count($rows) >= $rowLimit) {
                break;
            }

            try {
                $connection = $this->connectTo($tenant->database_name);

                if ($kind === 'read') {
                    foreach ($connection->select($this->withLimit($statement, $rowLimit - count($rows))) as $row) {
                        $rows[] = array_merge(['tenant_id' => $tenant->id, 'tenant_name' => $tenant->name], (array) $row);
                    }
                } elseif ($kind === 'write') {
                    $affected = $connection->affectingStatement($statement);
                    $rows[] = ['tenant_id' => $tenant->id, 'tenant_name' => $tenant->name, 'affected_rows' => $affected];
                } else {
                    $connection->statement($statement);
                    $rows[] = ['tenant_id' => $tenant->id, 'tenant_name' => $tenant->name, 'result' => 'Statement executed successfully.'];
                }
            } catch (Throwable $exception) {
                $rows[] = ['tenant_id' => $tenant->id, 'tenant_name' => $tenant->name, 'error' => $exception->getMessage()];
            }
        }

        return [
            'columns' => $rows ? array_keys($rows[0]) : ['tenant_id', 'tenant_name'],
            'rows' => $rows,
            'row_count' => count($rows),
            'truncated' => $kind === 'read' && count($rows) >= $rowLimit,
        ];
    }

    private function withLimit(string $sql, int $rowLimit): string
    {
        if (preg_match('/\bLIMIT\s+\d+/i', $sql)) {
            return $sql;
        }

        return rtrim($sql, "; \t\n\r").' LIMIT '.max(1, $rowLimit);
    }
}

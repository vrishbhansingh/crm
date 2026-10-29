<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Leads and Deals lists filter by status, priority, source, type,
 * product, state/city, created date, follow-up date and close date, and the
 * Kanban board pages each stage. None of those columns had an index, so every
 * filter was a full table scan — fine at a few hundred rows, painful at
 * thousands. Each index is added on its own and skipped (not fatal) if that
 * column can't take one or it already exists, so one oddity in an older
 * tenant database never blocks the others.
 */
return new class extends Migration
{
    private const INDEXES = [
        'leads' => [
            'leads_lead_status_index' => ['lead_status'],
            'leads_priority_index' => ['priority'],
            'leads_lead_source_index' => ['lead_source'],
            'leads_lead_type_index' => ['lead_type'],
            'leads_product_index' => ['product'],
            'leads_state_city_index' => ['state', 'city'],
            'leads_created_at_index' => ['created_at'],
            'leads_follow_up_date_index' => ['follow_up_date'],
        ],
        'deals' => [
            'deals_status_index' => ['status'],
            'deals_expected_close_date_index' => ['expected_close_date'],
            'deals_board_paging_index' => ['pipeline_id', 'stage_id', 'id'],
        ],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($indexes as $name => $columns) {
                if (! Schema::hasColumns($table, $columns) || $this->hasIndex($table, $name)) {
                    continue;
                }

                try {
                    Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns, $name));
                } catch (\Throwable $exception) {
                    // e.g. a column type that can't be indexed in this old database: skip it.
                }
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            foreach (array_keys($indexes) as $name) {
                if (Schema::hasTable($table) && $this->hasIndex($table, $name)) {
                    Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($name));
                }
            }
        }
    }

    private function hasIndex(string $table, string $name): bool
    {
        return collect(Schema::getConnection()->select('SHOW INDEX FROM `'.$table.'`'))->contains(fn ($row) => $row->Key_name === $name);
    }
};

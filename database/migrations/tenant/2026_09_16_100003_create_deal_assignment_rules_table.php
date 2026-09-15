<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('deal_assignment_rules')) {
            return;
        }

        Schema::create('deal_assignment_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            // 'pipeline' | 'source' | 'round_robin' — checked in this
            // priority order by DealAssignmentService; round_robin is the
            // catch-all with no match_value, at most one active row per
            // tenant (enforced in the controller, not the schema) — same
            // shape as lead_assignment_rules, mirrored for deals.
            $table->string('rule_type', 20);
            // For 'pipeline' rules this holds the pipeline_id (as a
            // string); for 'source' rules the lead_source-style value.
            // Null for round_robin.
            $table->string('match_value', 150)->nullable();
            $table->json('agent_ids');
            $table->unsignedInteger('cursor')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'rule_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_assignment_rules');
    }
};

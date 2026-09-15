<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lead_assignment_rules')) {
            return;
        }

        Schema::create('lead_assignment_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            // 'product' | 'state' | 'source' | 'round_robin' — checked in
            // this priority order (see LeadAssignmentService); round_robin
            // is the catch-all with no match_value, at most one active row
            // per tenant, enforced in the controller rather than the schema.
            $table->string('rule_type', 20);
            // What a lead's product/state/lead_source must equal (matched
            // case-insensitively) for this rule to apply — null for the
            // round_robin type, which always applies as the final fallback.
            $table->string('match_value', 150)->nullable();
            // Ordered pool of user ids this rule round-robins across —
            // plain ints, no FK: User lives on the central connection,
            // this table on the tenant connection, same cross-database
            // reasoning as leads.assigned_to.
            $table->json('agent_ids');
            // Index into agent_ids for the next assignment from this rule —
            // advances (wrapping) every time the rule actually assigns
            // someone, so consecutive matching leads cycle evenly.
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
        Schema::dropIfExists('lead_assignment_rules');
    }
};

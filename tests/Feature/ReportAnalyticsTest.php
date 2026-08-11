<?php

namespace Tests\Feature;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\Order;
use App\Models\PaymentDetails;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ReportAnalyticsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_report_totals_are_tenant_isolated(): void
    {
        $tenant = $this->tenant('main');
        $user = $this->user($tenant, 'manager');
        Role::findOrCreate('Manager', 'web');
        $user->assignRole('Manager');
        $user->givePermissionTo(Permission::findOrCreate('reports.view', 'web'));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($user, 'web')->withSession(['session_token' => $user->session_token]);

        [$pipeline, $stage] = $this->pipeline($tenant);
        Lead::create(['tenant_id' => $tenant->id, 'name' => 'Own Lead', 'assigned_to' => $user->id, 'lead_source' => 'website', 'status' => 'Active', 'is_converted' => 'No']);
        Deal::create(['tenant_id' => $tenant->id, 'pipeline_id' => $pipeline->id, 'stage_id' => $stage->id, 'owner_id' => $user->id, 'created_by' => $user->id, 'name' => 'Own Deal', 'amount' => 500, 'status' => 'won', 'closed_at' => now()]);
        $order = Order::create(['tenant_id' => $tenant->id, 'order_number' => 'REPORT-'.Str::random(8), 'invoice_id' => 'RI'.Str::random(8), 'invoice_date' => now(), 'user_id' => $user->id, 'net_amount' => 500, 'total_amount' => 500, 'paid_amount' => 200, 'due_amount' => 300]);
        PaymentDetails::create(['tenant_id' => $tenant->id, 'order_id' => $order->id, 'payment_mode' => 'bank_transfer', 'payment_date' => now(), 'paid_amount' => 200]);

        $other = $this->tenant('other');
        $otherUser = $this->user($other, 'other');
        Lead::withoutGlobalScopes()->create(['tenant_id' => $other->id, 'name' => 'Foreign Lead', 'assigned_to' => $otherUser->id, 'status' => 'Active', 'is_converted' => 'No']);
        Order::withoutGlobalScopes()->create(['tenant_id' => $other->id, 'order_number' => 'FOREIGN-'.Str::random(8), 'invoice_id' => 'FI'.Str::random(8), 'invoice_date' => now(), 'user_id' => $otherUser->id, 'net_amount' => 9000, 'total_amount' => 9000]);

        $this->getJson('/reports/data')->assertOk()
            ->assertJsonPath('summary.leads_created', 1)
            ->assertJsonPath('summary.deals_created', 1)
            ->assertJsonPath('summary.won_deals', 1)
            ->assertJsonPath('summary.booked_revenue', 500)
            ->assertJsonPath('summary.cash_collected', 200);
    }

    private function tenant(string $label): Tenant
    {
        return Tenant::create(['name' => "Report {$label}", 'slug' => 'report-'.$label.'-'.Str::lower(Str::random(8)), 'status' => 'Active']);
    }

    private function user(Tenant $tenant, string $label): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create(['tenant_id' => $tenant->id, 'name' => "Report {$label}", 'email' => "report-{$label}-{$suffix}@example.test", 'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => 'report-'.$suffix]);
    }

    private function pipeline(Tenant $tenant): array
    {
        $pipeline = Pipeline::create(['tenant_id' => $tenant->id, 'name' => 'Report Pipeline', 'is_default' => true, 'is_active' => true]);
        $stage = PipelineStage::create(['tenant_id' => $tenant->id, 'pipeline_id' => $pipeline->id, 'name' => 'Won', 'sort_order' => 1, 'is_won' => true]);

        return [$pipeline, $stage];
    }
}

<?php

namespace Tests\Feature;

use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_team_dashboard_data_includes_tasks_deals_and_user_counts(): void
    {
        $tenant = Tenant::create(['name' => 'Dash Tenant Two', 'slug' => 'dash-two-'.Str::random(8), 'status' => 'Active']);
        $admin = $this->user($tenant, 'Admin');

        $this->actingAs($admin)->withSession(['session_token' => $admin->session_token])
            ->getJson(route('dashboard.data'))
            ->assertOk()
            ->assertJsonPath('scope', 'team')
            ->assertJsonStructure([
                'data' => ['totalLead', 'tasksDueToday', 'openDeals', 'pipelineValue', 'totalCompanies', 'activeCampaigns', 'totalTemplates', 'totalUsers'],
                'charts' => ['leadsTrend', 'leadsByStatus', 'dealsByStage'],
                'followUps',
                'closingSoon',
            ]);
    }

    public function test_open_deals_and_pipeline_value_exclude_won_and_lost_deals(): void
    {
        $tenant = Tenant::create(['name' => 'Dash Tenant Three', 'slug' => 'dash-three-'.Str::random(8), 'status' => 'Active']);
        $admin = $this->user($tenant, 'Admin');

        $pipeline = Pipeline::create(['tenant_id' => $tenant->id, 'name' => 'Sales']);
        $stage = PipelineStage::create(['tenant_id' => $tenant->id, 'pipeline_id' => $pipeline->id, 'name' => 'New']);
        Deal::create(['tenant_id' => $tenant->id, 'pipeline_id' => $pipeline->id, 'stage_id' => $stage->id, 'name' => 'Open Deal', 'amount' => 1000, 'status' => 'open']);
        Deal::create(['tenant_id' => $tenant->id, 'pipeline_id' => $pipeline->id, 'stage_id' => $stage->id, 'name' => 'Won Deal', 'amount' => 5000, 'status' => 'won']);
        Deal::create(['tenant_id' => $tenant->id, 'pipeline_id' => $pipeline->id, 'stage_id' => $stage->id, 'name' => 'Lost Deal', 'amount' => 2000, 'status' => 'lost']);

        $this->actingAs($admin)->withSession(['session_token' => $admin->session_token])
            ->getJson(route('dashboard.data'))
            ->assertOk()
            ->assertJsonPath('data.openDeals', 1)
            ->assertJsonPath('data.pipelineValue', 1000);
    }

    private function user(Tenant $tenant, string $role): User
    {
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $role,
            'email' => Str::random(10).'@example.test',
            'password' => Hash::make('password'),
            'status' => 'Active',
            'session_token' => Str::random(60),
        ]);
        Role::findOrCreate($role, 'web');
        $user->assignRole($role);

        return $user;
    }
}

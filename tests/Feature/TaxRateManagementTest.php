<?php

namespace Tests\Feature;

use App\Models\TaxRate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TaxRateManagementTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $suffix = Str::lower(Str::random(8));
        $this->tenant = Tenant::create(['name' => 'Tax Rate Co', 'slug' => 'tax-rate-'.$suffix, 'status' => 'Active']);
        $this->user = User::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Tax Rate User',
            'email' => "tax-rate-{$suffix}@example.test", 'password' => Hash::make('password'),
            'status' => 'Active', 'session_token' => 'tax-rate-'.$suffix,
        ]);
        foreach (['masters.view', 'masters.create', 'masters.edit', 'masters.delete'] as $permission) {
            $this->user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->user, 'web')->withSession(['session_token' => $this->user->session_token]);
    }

    public function test_user_can_create_update_and_toggle_a_tax_rate(): void
    {
        $response = $this->postJson('/master-data/tax-rates', ['name' => 'GST 18%', 'rate_percent' => 18])->assertOk();
        $rate = TaxRate::findOrFail($response->json('id'));
        $this->assertSame($this->tenant->id, $rate->tenant_id);
        $this->assertSame('18.00', (string) $rate->rate_percent);

        $this->putJson("/master-data/tax-rates/{$rate->id}", ['name' => 'GST 18% (revised)', 'rate_percent' => 18])->assertOk();
        $this->assertSame('GST 18% (revised)', $rate->fresh()->name);

        $this->postJson("/master-data/tax-rates/{$rate->id}/toggle-status")->assertOk();
        $this->assertFalse((bool) $rate->fresh()->is_active);
    }

    public function test_tax_rate_percent_must_be_a_valid_percentage(): void
    {
        $this->postJson('/master-data/tax-rates', ['name' => 'Bad rate', 'rate_percent' => 150])->assertUnprocessable();
        $this->assertDatabaseMissing('tax_rates', ['name' => 'Bad rate']);
    }

    public function test_data_endpoint_only_returns_the_current_tenants_rates(): void
    {
        TaxRate::create(['tenant_id' => $this->tenant->id, 'name' => 'GST 5%', 'rate_percent' => 5]);

        $other = Tenant::create(['name' => 'Other Tax Co', 'slug' => 'other-tax-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        TaxRate::withoutGlobalScopes()->create(['tenant_id' => $other->id, 'name' => 'Foreign Rate', 'rate_percent' => 12]);

        $response = $this->getJson('/master-data/tax-rates')->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('GST 5%'));
        $this->assertFalse($names->contains('Foreign Rate'));
    }
}

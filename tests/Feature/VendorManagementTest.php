<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class VendorManagementTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $suffix = Str::lower(Str::random(8));
        $this->tenant = Tenant::create(['name' => 'Vendor Co', 'slug' => 'vendor-'.$suffix, 'status' => 'Active']);
        $this->user = User::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Vendor User',
            'email' => "vendor-{$suffix}@example.test", 'password' => Hash::make('password'),
            'status' => 'Active', 'session_token' => 'vendor-'.$suffix,
        ]);
        foreach (['vendors.view', 'vendors.create', 'vendors.edit', 'vendors.delete'] as $permission) {
            $this->user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->user, 'web')->withSession(['session_token' => $this->user->session_token]);
    }

    public function test_vendors_page_renders(): void
    {
        $this->get('/vendors')->assertOk();
    }

    public function test_user_can_create_update_and_delete_a_vendor(): void
    {
        $response = $this->postJson('/vendors', [
            'name' => 'Acme Supplies', 'gst_number' => '27AAAAA0000A1Z5', 'state' => 'Maharashtra', 'status' => 'Active',
        ])->assertOk();
        $vendor = Vendor::findOrFail($response->json('id'));
        $this->assertSame($this->tenant->id, $vendor->tenant_id);

        $this->putJson("/vendors/{$vendor->id}", ['name' => 'Acme Supplies Pvt Ltd', 'status' => 'Active'])->assertOk();
        $this->assertSame('Acme Supplies Pvt Ltd', $vendor->fresh()->name);

        $this->deleteJson("/vendors/{$vendor->id}")->assertOk();
        $this->assertNull(Vendor::find($vendor->id));
    }

    public function test_data_endpoint_only_returns_the_current_tenants_vendors(): void
    {
        Vendor::create(['tenant_id' => $this->tenant->id, 'name' => 'My Vendor', 'status' => 'Active']);

        $other = Tenant::create(['name' => 'Other Vendor Co', 'slug' => 'other-vendor-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        Vendor::withoutGlobalScopes()->create(['tenant_id' => $other->id, 'name' => 'Foreign Vendor', 'status' => 'Active']);

        $response = $this->getJson('/vendors/data')->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('My Vendor'));
        $this->assertFalse($names->contains('Foreign Vendor'));
    }
}

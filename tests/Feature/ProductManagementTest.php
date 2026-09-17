<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\TaxRate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $suffix = Str::lower(Str::random(8));
        $this->tenant = Tenant::create(['name' => 'Product Co', 'slug' => 'product-'.$suffix, 'status' => 'Active']);
        $this->user = User::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Product User',
            'email' => "product-{$suffix}@example.test", 'password' => Hash::make('password'),
            'status' => 'Active', 'session_token' => 'product-'.$suffix,
        ]);
        foreach (['products.view', 'products.create', 'products.edit', 'products.delete'] as $permission) {
            $this->user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->user, 'web')->withSession(['session_token' => $this->user->session_token]);
    }

    public function test_user_can_create_and_update_a_product_with_a_tax_rate(): void
    {
        $taxRate = TaxRate::create(['tenant_id' => $this->tenant->id, 'name' => 'GST 18%', 'rate_percent' => 18]);

        $response = $this->postJson('/products', [
            'name' => 'Widget A', 'sku' => 'WID-A', 'category' => 'hardware', 'uom' => 'nos',
            'unit_price' => 499.50, 'tax_rate_id' => $taxRate->id, 'status' => 'Active',
        ])->assertOk();

        $product = Product::findOrFail($response->json('id'));
        $this->assertSame($this->tenant->id, $product->tenant_id);
        $this->assertSame('499.50', (string) $product->unit_price);

        $this->putJson("/products/{$product->id}", [
            'name' => 'Widget A (v2)', 'unit_price' => 599, 'status' => 'Active',
        ])->assertOk();
        $this->assertSame('Widget A (v2)', $product->fresh()->name);
    }

    public function test_product_rejects_a_tax_rate_from_another_tenant(): void
    {
        $other = Tenant::create(['name' => 'Other Product Co', 'slug' => 'other-product-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $foreignRate = TaxRate::withoutGlobalScopes()->create(['tenant_id' => $other->id, 'name' => 'Foreign GST', 'rate_percent' => 12]);

        $this->postJson('/products', [
            'name' => 'Bad Product', 'unit_price' => 100, 'tax_rate_id' => $foreignRate->id, 'status' => 'Active',
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('products', ['name' => 'Bad Product']);
    }

    public function test_options_endpoint_only_lists_active_products_for_the_current_tenant(): void
    {
        Product::create(['tenant_id' => $this->tenant->id, 'name' => 'Active One', 'unit_price' => 10, 'status' => 'Active']);
        Product::create(['tenant_id' => $this->tenant->id, 'name' => 'Inactive One', 'unit_price' => 20, 'status' => 'Inactive']);

        $other = Tenant::create(['name' => 'Other Options Co', 'slug' => 'other-options-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        Product::withoutGlobalScopes()->create(['tenant_id' => $other->id, 'name' => 'Foreign Product', 'unit_price' => 30, 'status' => 'Active']);

        $names = collect($this->getJson('/products/options')->assertOk()->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Active One'));
        $this->assertFalse($names->contains('Inactive One'));
        $this->assertFalse($names->contains('Foreign Product'));
    }
}

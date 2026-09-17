<?php

namespace Tests\Feature;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\TaxRate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class QuotationManagementTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $suffix = Str::lower(Str::random(8));
        $this->tenant = Tenant::create(['name' => 'Quote Co', 'slug' => 'quote-'.$suffix, 'status' => 'Active']);
        $this->user = User::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Quote User',
            'email' => "quote-{$suffix}@example.test", 'password' => Hash::make('password'),
            'status' => 'Active', 'session_token' => 'quote-'.$suffix,
        ]);
        foreach (['quotations.view', 'quotations.create', 'quotations.edit', 'quotations.delete', 'products.view'] as $permission) {
            $this->user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->user, 'web')->withSession(['session_token' => $this->user->session_token]);
    }

    private function pipeline(): array
    {
        $pipeline = Pipeline::create(['tenant_id' => $this->tenant->id, 'name' => 'Quote Pipeline', 'is_active' => true, 'sort_order' => 0]);
        $stage = PipelineStage::create(['tenant_id' => $this->tenant->id, 'pipeline_id' => $pipeline->id, 'name' => 'Open', 'sort_order' => 1]);

        return [$pipeline, $stage];
    }

    public function test_user_can_create_a_quotation_from_a_lead_and_it_gets_a_number(): void
    {
        $lead = Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'Quote Lead', 'assigned_to' => $this->user->id, 'status' => 'Active', 'is_converted' => 'No']);

        $response = $this->postJson('/quotations', ['lead_id' => $lead->id])->assertOk();
        $quotation = Quotation::findOrFail($response->json('id'));

        $this->assertSame($this->tenant->id, $quotation->tenant_id);
        $this->assertSame($lead->id, $quotation->lead_id);
        $this->assertSame('draft', $quotation->status);
        $this->assertSame(1, $quotation->version);
        $this->assertStringStartsWith('QT-'.now()->format('Y').'-', $quotation->quotation_number);
    }

    public function test_user_can_create_a_quotation_from_a_deal(): void
    {
        [$pipeline, $stage] = $this->pipeline();
        $deal = Deal::create(['tenant_id' => $this->tenant->id, 'pipeline_id' => $pipeline->id, 'stage_id' => $stage->id, 'owner_id' => $this->user->id, 'name' => 'Quote Deal', 'amount' => 1000, 'status' => 'open']);

        $response = $this->postJson('/quotations', ['deal_id' => $deal->id])->assertOk();
        $quotation = Quotation::findOrFail($response->json('id'));
        $this->assertSame($deal->id, $quotation->deal_id);
        $this->assertNull($quotation->lead_id);
    }

    public function test_quotation_requires_exactly_one_of_lead_or_deal(): void
    {
        $this->postJson('/quotations', [])->assertUnprocessable();

        $lead = Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'Both Lead', 'status' => 'Active', 'is_converted' => 'No']);
        [$pipeline, $stage] = $this->pipeline();
        $deal = Deal::create(['tenant_id' => $this->tenant->id, 'pipeline_id' => $pipeline->id, 'stage_id' => $stage->id, 'owner_id' => $this->user->id, 'name' => 'Both Deal', 'amount' => 500, 'status' => 'open']);

        $this->postJson('/quotations', ['lead_id' => $lead->id, 'deal_id' => $deal->id])->assertUnprocessable();
    }

    public function test_adding_and_removing_items_recalculates_totals(): void
    {
        $lead = Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'Totals Lead', 'status' => 'Active', 'is_converted' => 'No']);
        $taxRate = TaxRate::create(['tenant_id' => $this->tenant->id, 'name' => 'GST 18%', 'rate_percent' => 18]);
        $quotation = Quotation::create(['tenant_id' => $this->tenant->id, 'quotation_number' => 'QT-TEST-1', 'lead_id' => $lead->id, 'status' => 'draft', 'version' => 1, 'owner_id' => $this->user->id, 'created_by' => $this->user->id]);

        $this->postJson("/quotations/{$quotation->id}/items", [
            'description' => 'Widget', 'uom' => 'nos', 'quantity' => 2, 'unit_price' => 100,
            'discount_percent' => 10, 'tax_rate_id' => $taxRate->id,
        ])->assertOk();

        $quotation->refresh();
        // gross 200, less 10% discount = 180, plus 18% tax = 212.40
        $this->assertSame('200.00', (string) $quotation->sub_total);
        $this->assertSame('20.00', (string) $quotation->discount_amount);
        $this->assertSame('32.40', (string) $quotation->tax_amount);
        $this->assertSame('212.40', (string) $quotation->total_amount);

        $item = $quotation->items()->firstOrFail();
        $this->putJson("/quotations/{$quotation->id}/items/{$item->id}", [
            'description' => 'Widget', 'quantity' => 1, 'unit_price' => 100, 'discount_percent' => 0,
        ])->assertOk();
        $this->assertSame('100.00', (string) $quotation->fresh()->sub_total);

        $this->deleteJson("/quotations/{$quotation->id}/items/{$item->id}")->assertOk();
        $this->assertSame('0.00', (string) $quotation->fresh()->sub_total);
    }

    public function test_product_line_item_snapshots_price_so_later_product_edits_dont_change_it(): void
    {
        $lead = Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'Snapshot Lead', 'status' => 'Active', 'is_converted' => 'No']);
        $product = Product::create(['tenant_id' => $this->tenant->id, 'name' => 'Gadget', 'unit_price' => 50, 'status' => 'Active']);
        $quotation = Quotation::create(['tenant_id' => $this->tenant->id, 'quotation_number' => 'QT-TEST-2', 'lead_id' => $lead->id, 'status' => 'draft', 'version' => 1, 'owner_id' => $this->user->id, 'created_by' => $this->user->id]);

        $this->postJson("/quotations/{$quotation->id}/items", [
            'product_id' => $product->id, 'description' => 'Gadget', 'quantity' => 1, 'unit_price' => 50,
        ])->assertOk();

        $product->update(['unit_price' => 999]);

        $item = $quotation->items()->firstOrFail();
        $this->assertSame('50.00', (string) $item->unit_price);
    }

    public function test_editing_is_blocked_once_a_quotation_leaves_draft_status(): void
    {
        $lead = Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'Locked Lead', 'status' => 'Active', 'is_converted' => 'No']);
        $quotation = Quotation::create(['tenant_id' => $this->tenant->id, 'quotation_number' => 'QT-TEST-3', 'lead_id' => $lead->id, 'status' => 'sent', 'version' => 1, 'owner_id' => $this->user->id, 'created_by' => $this->user->id]);

        $this->putJson("/quotations/{$quotation->id}", ['currency' => 'USD'])->assertUnprocessable();
        $this->postJson("/quotations/{$quotation->id}/items", ['description' => 'X', 'quantity' => 1, 'unit_price' => 1])->assertUnprocessable();
    }

    public function test_quotation_from_another_tenant_is_not_visible(): void
    {
        $other = Tenant::create(['name' => 'Other Quote Co', 'slug' => 'other-quote-'.Str::lower(Str::random(8)), 'status' => 'Active']);
        $foreignLead = Lead::withoutGlobalScopes()->create(['tenant_id' => $other->id, 'name' => 'Foreign Lead', 'status' => 'Active', 'is_converted' => 'No']);
        $foreignQuote = Quotation::withoutGlobalScopes()->create(['tenant_id' => $other->id, 'quotation_number' => 'QT-OTHER-1', 'lead_id' => $foreignLead->id, 'status' => 'draft', 'version' => 1]);

        $this->getJson("/quotations/{$foreignQuote->id}/detail")->assertNotFound();
    }

    public function test_pdf_route_returns_a_pdf_document(): void
    {
        $lead = Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'PDF Lead', 'email' => 'pdf@example.test', 'status' => 'Active', 'is_converted' => 'No']);
        $quotation = Quotation::create(['tenant_id' => $this->tenant->id, 'quotation_number' => 'QT-TEST-4', 'lead_id' => $lead->id, 'status' => 'draft', 'version' => 1, 'owner_id' => $this->user->id, 'created_by' => $this->user->id]);

        $response = $this->get("/quotations/{$quotation->id}/pdf");
        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_index_and_show_pages_render(): void
    {
        $lead = Lead::create(['tenant_id' => $this->tenant->id, 'name' => 'Page Lead', 'status' => 'Active', 'is_converted' => 'No']);
        $quotation = Quotation::create(['tenant_id' => $this->tenant->id, 'quotation_number' => 'QT-TEST-5', 'lead_id' => $lead->id, 'status' => 'draft', 'version' => 1, 'owner_id' => $this->user->id, 'created_by' => $this->user->id]);

        $this->get('/quotations')->assertOk();
        $this->get('/quotations/create')->assertOk();
        $this->get("/quotations/{$quotation->id}")->assertOk();
    }
}

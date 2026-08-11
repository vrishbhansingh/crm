<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CompanyContactTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix = Str::lower(Str::random(10));
        $this->tenant = Tenant::create(['name' => 'Company QA '.$suffix, 'slug' => 'company-qa-'.$suffix, 'status' => 'Active']);
        $this->user = $this->createUser($this->tenant, 'primary');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->user, 'web')->withSession(['session_token' => $this->user->session_token]);
    }

    public function test_company_and_contact_pages_render(): void
    {
        $this->grant('companies.view', 'contacts.view');

        $this->get('/companies')->assertOk()->assertSee('Companies');
        $this->get('/contacts')->assertOk()->assertSee('Contacts');
    }

    public function test_company_and_contact_crud_preserves_tenant_relationships(): void
    {
        $this->grant('companies.create', 'contacts.create');

        $companyResponse = $this->postJson('/companies', [
            'name' => 'Acme QA',
            'status' => 'prospect',
            'owner_id' => $this->user->id,
            'email' => 'sales@acme.example',
        ])->assertOk();

        $companyId = $companyResponse->json('id');
        $contactResponse = $this->postJson('/contacts', [
            'company_id' => $companyId,
            'owner_id' => $this->user->id,
            'name' => 'Asha Contact',
            'email' => 'asha@acme.example',
            'status' => 'active',
            'is_primary' => true,
        ])->assertOk();

        $contactId = $contactResponse->json('id');
        $this->assertDatabaseHas('companies', ['id' => $companyId, 'tenant_id' => $this->tenant->id]);
        $this->assertDatabaseHas('contacts', ['id' => $contactId, 'tenant_id' => $this->tenant->id, 'company_id' => $companyId]);
    }

    public function test_contact_rejects_a_company_from_another_tenant(): void
    {
        $this->grant('contacts.create');
        $otherTenant = $this->createTenant('other');
        $otherCompany = Company::create(['tenant_id' => $otherTenant->id, 'name' => 'Other Company', 'status' => 'prospect']);

        $this->postJson('/contacts', [
            'company_id' => $otherCompany->id,
            'name' => 'Invalid Contact',
            'status' => 'active',
        ])->assertUnprocessable()->assertJsonValidationErrors('company_id');
    }

    public function test_non_elevated_user_cannot_edit_another_owners_company_or_contact(): void
    {
        $this->grant('companies.edit', 'contacts.edit');
        $otherUser = $this->createUser($this->tenant, 'other-owner');
        $company = Company::create(['tenant_id' => $this->tenant->id, 'owner_id' => $otherUser->id, 'name' => 'Private Company', 'status' => 'prospect']);
        $contact = Contact::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'owner_id' => $otherUser->id, 'name' => 'Private Contact', 'status' => 'active']);

        $this->putJson("/companies/{$company->id}", ['name' => 'Changed', 'status' => 'customer'])->assertNotFound();
        $this->putJson("/contacts/{$contact->id}", ['name' => 'Changed', 'status' => 'active'])->assertNotFound();
    }

    public function test_linked_company_cannot_be_deleted(): void
    {
        $this->grant('companies.delete');
        $company = Company::create(['tenant_id' => $this->tenant->id, 'owner_id' => $this->user->id, 'name' => 'Linked Company', 'status' => 'prospect']);
        Contact::create(['tenant_id' => $this->tenant->id, 'company_id' => $company->id, 'owner_id' => $this->user->id, 'name' => 'Linked Contact', 'status' => 'active']);

        $this->deleteJson("/companies/{$company->id}")->assertUnprocessable();
        $this->assertDatabaseHas('companies', ['id' => $company->id, 'deleted_at' => null]);
    }

    private function grant(string ...$permissions): void
    {
        foreach ($permissions as $permission) {
            $this->user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function createTenant(string $label): Tenant
    {
        $suffix = Str::lower(Str::random(10));

        return Tenant::create(['name' => "QA {$label}", 'slug' => "qa-{$label}-{$suffix}", 'status' => 'Active']);
    }

    private function createUser(Tenant $tenant, string $label): User
    {
        $suffix = Str::lower(Str::random(10));

        return User::create([
            'tenant_id' => $tenant->id,
            'name' => 'QA '.$label,
            'email' => "qa-{$label}-{$suffix}@example.test",
            'password' => Hash::make('password'),
            'status' => 'Active',
            'session_token' => 'qa-session-'.$suffix,
        ]);
    }
}

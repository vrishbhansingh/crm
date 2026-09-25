<?php

namespace Tests\Feature;

use App\Mail\CampaignMail;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateAttachment;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EmailTemplateAttachmentTest extends TestCase
{
    use DatabaseTransactions;

    private Tenant $tenant;

    private User $user;

    private EmailTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $suffix = Str::lower(Str::random(10));
        $this->tenant = Tenant::create(['name' => "Attach Tenant {$suffix}", 'slug' => "attach-{$suffix}", 'status' => 'Active']);
        $this->user = User::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Manager', 'email' => "attach-{$suffix}@example.test",
            'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(60),
        ]);
        foreach (['templates.view', 'templates.create', 'templates.edit', 'templates.delete', 'campaigns.view', 'campaigns.create', 'campaigns.send'] as $permission) {
            $this->user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->user, 'web')->withSession(['session_token' => $this->user->session_token]);

        $this->template = EmailTemplate::create([
            'tenant_id' => $this->tenant->id, 'created_by' => $this->user->id,
            'name' => 'Brochure template', 'subject' => 'Hello', 'body' => '<p>Hi</p>',
        ]);

        Storage::fake('local');
    }

    public function test_an_image_upload_returns_a_public_url_and_saves_the_file(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 200, 200)->size(500);

        $response = $this->postJson(route('templates.upload_image'), ['file' => $file])
            ->assertOk()->json();

        $this->assertTrue($response['status']);
        $this->assertStringContainsString('/uploads/email-templates/images/', $response['url']);

        $relativePath = ltrim(parse_url($response['url'], PHP_URL_PATH), '/');
        // The route prefix (e.g. /crm/public/...) varies by install, so just
        // confirm a file matching the generated name actually landed on disk.
        $fileName = basename($relativePath);
        $this->assertFileExists(public_path('uploads/email-templates/images/'.$fileName));

        @unlink(public_path('uploads/email-templates/images/'.$fileName));
    }

    public function test_an_oversized_or_wrong_type_image_is_rejected(): void
    {
        $badType = UploadedFile::fake()->create('brochure.pdf', 100, 'application/pdf');
        $this->postJson(route('templates.upload_image'), ['file' => $badType])
            ->assertStatus(422);

        $tooBig = UploadedFile::fake()->image('huge.jpg')->size(6000); // > 5MB
        $this->postJson(route('templates.upload_image'), ['file' => $tooBig])
            ->assertStatus(422);
    }

    public function test_a_document_can_be_attached_to_a_template_and_listed(): void
    {
        $file = UploadedFile::fake()->create('pricelist.pdf', 200, 'application/pdf');

        $response = $this->postJson(route('templates.attachments.store', $this->template->id), ['file' => $file])
            ->assertOk()->json();

        $this->assertTrue($response['status']);
        $this->assertSame('pricelist.pdf', $response['data']['original_name']);

        $attachment = EmailTemplateAttachment::first();
        $this->assertNotNull($attachment);
        $this->assertSame($this->template->id, $attachment->email_template_id);
        $this->assertSame($this->tenant->id, $attachment->tenant_id);
        Storage::disk('local')->assertExists($attachment->stored_path);

        $this->get(route('templates.edit', $this->template->id))
            ->assertOk()
            ->assertSee('pricelist.pdf');
    }

    public function test_an_attachment_with_a_disallowed_type_is_rejected(): void
    {
        $file = UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload');
        $this->postJson(route('templates.attachments.store', $this->template->id), ['file' => $file])
            ->assertStatus(422);
    }

    public function test_removing_an_attachment_deletes_the_row_and_the_file(): void
    {
        $file = UploadedFile::fake()->create('quote.pdf', 100, 'application/pdf');
        $this->postJson(route('templates.attachments.store', $this->template->id), ['file' => $file])->assertOk();
        $attachment = EmailTemplateAttachment::first();
        $path = $attachment->stored_path;

        $this->delete(route('templates.attachments.destroy', $attachment->id))
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->assertDatabaseMissing('email_template_attachments', ['id' => $attachment->id]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_deleting_a_template_cleans_up_its_attachment_files(): void
    {
        $file = UploadedFile::fake()->create('quote.pdf', 100, 'application/pdf');
        $this->postJson(route('templates.attachments.store', $this->template->id), ['file' => $file])->assertOk();
        $attachment = EmailTemplateAttachment::first();
        $path = $attachment->stored_path;

        $this->delete(route('templates.destroy', $this->template->id))->assertOk();

        Storage::disk('local')->assertMissing($path);
        $this->assertDatabaseMissing('email_template_attachments', ['id' => $attachment->id]);
    }

    public function test_a_campaign_sent_from_a_template_with_an_attachment_actually_attaches_it(): void
    {
        Mail::fake();

        Lead::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Prospect', 'email' => 'prospect@example.test',
            'lead_status' => 'Hot', 'status' => 'Active', 'is_converted' => 'No',
        ]);

        $file = UploadedFile::fake()->create('brochure.pdf', 150, 'application/pdf');
        $this->postJson(route('templates.attachments.store', $this->template->id), ['file' => $file])->assertOk();

        $store = $this->postJson(route('campaigns.store'), [
            'name' => 'Brochure campaign', 'email_template_id' => $this->template->id,
            'audience_type' => 'leads', 'filters' => ['lead_status' => 'Hot'],
        ])->assertOk()->json();
        $campaign = EmailCampaign::findOrFail($store['data']['id']);

        $this->postJson(route('campaigns.send', $campaign))->assertOk();

        Mail::assertSent(CampaignMail::class, function (CampaignMail $mail) {
            return $mail->hasTo('prospect@example.test')
                && count($mail->filesToAttach) === 1
                && $mail->filesToAttach[0]['name'] === 'brochure.pdf';
        });
    }
}

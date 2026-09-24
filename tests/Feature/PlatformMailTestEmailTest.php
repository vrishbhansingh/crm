<?php

namespace Tests\Feature;

use App\Models\PlatformMailSetting;
use App\Models\User;
use App\Support\PermissionTeam;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlatformMailTestEmailTest extends TestCase
{
    use DatabaseTransactions;

    private User $super;

    protected function setUp(): void
    {
        parent::setUp();

        $this->super = User::create([
            'tenant_id' => null,
            'name' => 'Super Admin',
            'email' => Str::random(10).'@example.test',
            'password' => Hash::make('password'),
            'status' => 'Active',
            'session_token' => Str::random(60),
        ]);
        PermissionTeam::run(null, function () {
            Role::findOrCreate('Super Admin', 'web');
            $this->super->assignRole('Super Admin');
        });

        PlatformMailSetting::current()->update([
            'smtp_host' => 'smtp.example.test',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => 'noreply@example.test',
            'smtp_password' => 'secret',
            'smtp_from_address' => 'noreply@example.test',
            'smtp_from_name' => 'CRM',
        ]);

        $this->post('/superadmin/login', ['email' => $this->super->email, 'password' => 'password'])
            ->assertRedirect('/superadmin');
    }

    public function test_a_custom_address_receives_the_test_email_instead_of_the_super_admins_own(): void
    {
        // Mail::raw() is a no-op under Mail::fake() in this Laravel version
        // (nothing to introspect), so the session's success message — which
        // the controller builds from the same $to it mails — is what proves
        // which address was actually used.
        Mail::fake();

        $this->post(route('superadmin.settings.mail.test'), ['test_email' => 'someone-else@example.test'])
            ->assertRedirect()
            ->assertSessionHas('success', 'Test email sent to someone-else@example.test.');
    }

    public function test_leaving_the_field_blank_falls_back_to_the_super_admins_own_email(): void
    {
        Mail::fake();

        $this->post(route('superadmin.settings.mail.test'), [])
            ->assertRedirect()
            ->assertSessionHas('success', 'Test email sent to '.$this->super->email.'.');
    }

    public function test_an_invalid_address_is_rejected(): void
    {
        Mail::fake();

        $this->post(route('superadmin.settings.mail.test'), ['test_email' => 'not-an-email'])
            ->assertSessionHasErrors('test_email');
    }
}

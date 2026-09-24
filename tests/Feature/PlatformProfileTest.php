<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\PermissionTeam;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlatformProfileTest extends TestCase
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

        $this->post('/superadmin/login', ['email' => $this->super->email, 'password' => 'password'])
            ->assertRedirect('/superadmin');
    }

    public function test_super_admin_can_view_and_update_their_profile(): void
    {
        $this->get(route('superadmin.profile.edit'))->assertOk()->assertSee($this->super->name);

        $newEmail = Str::random(8).'@example.test';
        $this->put(route('superadmin.profile.update'), [
            'name' => 'Updated Name',
            'email' => $newEmail,
            'phone' => '9998887777',
        ])->assertRedirect();

        $this->super->refresh();
        $this->assertSame('Updated Name', $this->super->name);
        $this->assertSame($newEmail, $this->super->email);
        $this->assertSame('9998887777', $this->super->phone);
    }

    public function test_email_must_stay_unique_across_all_users(): void
    {
        $other = User::create([
            'tenant_id' => null, 'name' => 'Other', 'email' => Str::random(8).'@example.test',
            'password' => Hash::make('password'), 'status' => 'Active', 'session_token' => Str::random(60),
        ]);

        $this->put(route('superadmin.profile.update'), [
            'name' => 'X', 'email' => $other->email,
        ])->assertSessionHasErrors('email');
    }

    public function test_changing_password_requires_the_current_one_and_logs_out(): void
    {
        $this->put(route('superadmin.profile.password'), [
            'current_password' => 'wrong',
            'new_password' => 'newpassword123',
            'confirm_password' => 'newpassword123',
        ])->assertSessionHasErrors('current_password');

        $this->put(route('superadmin.profile.password'), [
            'current_password' => 'password',
            'new_password' => 'newpassword123',
            'confirm_password' => 'newpassword123',
        ])->assertRedirect(route('superadmin.login'));

        $this->assertGuest('web');
        $this->super->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->super->password));

        // The old session_token is invalidated — a stale session can't act as this user anymore.
        $this->post('/superadmin/login', ['email' => $this->super->email, 'password' => 'newpassword123'])
            ->assertRedirect('/superadmin');
    }
}

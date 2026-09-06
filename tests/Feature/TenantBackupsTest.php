<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TenantBackupsTest extends TestCase
{
    use DatabaseTransactions;

    private User $superAdmin;
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = storage_path('app/tenant-backups');
        File::ensureDirectoryExists($this->directory);

        $this->superAdmin = User::create([
            'tenant_id' => null,
            'name' => 'Backups QA',
            'email' => Str::lower(Str::random(10)).'@example.test',
            'password' => Hash::make('password'),
            'status' => 'Active',
            'session_token' => Str::random(60),
        ]);
        Role::findOrCreate('Super Admin', 'web');
        $this->superAdmin->givePermissionTo(Permission::findOrCreate('platform.manage-tenants', 'web'));
        $this->superAdmin->assignRole('Super Admin');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->superAdmin, 'web')
            ->withSession(['session_token' => $this->superAdmin->session_token]);
    }

    protected function tearDown(): void
    {
        File::delete($this->directory.'/qa-test-backup.sql');
        parent::tearDown();
    }

    public function test_backups_page_lists_files_and_offers_a_download_link(): void
    {
        File::put($this->directory.'/qa-test-backup.sql', "-- dummy backup\nINSERT INTO x VALUES (1);\n");

        $this->get('/superadmin/backups')
            ->assertOk()
            ->assertSee('qa-test-backup.sql')
            ->assertSee('superadmin/backups/qa-test-backup.sql/download', false);
    }

    public function test_a_backup_file_can_be_downloaded(): void
    {
        File::put($this->directory.'/qa-test-backup.sql', "-- dummy backup\n");

        $this->get('/superadmin/backups/qa-test-backup.sql/download')->assertOk();
    }

    public function test_a_backup_file_can_be_deleted(): void
    {
        File::put($this->directory.'/qa-test-backup.sql', "-- dummy backup\n");

        $this->delete('/superadmin/backups/qa-test-backup.sql')
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertFileDoesNotExist($this->directory.'/qa-test-backup.sql');
    }

    public function test_a_nonexistent_backup_filename_404s(): void
    {
        $this->get('/superadmin/backups/does-not-exist.sql/download')->assertNotFound();
    }

    /**
     * The {filename} route parameter can't itself carry a literal "/" (it
     * only matches within one path segment), so real directory traversal
     * isn't reachable through this route to begin with — but a file that
     * happens to sit in the same directory without a .sql extension (this
     * directory holds nothing else today, but might one day) must still be
     * refused, since resolve()'s job is to only ever serve our own dumps.
     */
    public function test_a_non_sql_file_in_the_backups_directory_is_refused(): void
    {
        File::put($this->directory.'/not-a-backup.txt', 'irrelevant');

        try {
            $this->get('/superadmin/backups/not-a-backup.txt/download')->assertNotFound();
        } finally {
            File::delete($this->directory.'/not-a-backup.txt');
        }
    }
}

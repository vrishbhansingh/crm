# Handoff for Claude

Updated: 2026-08-14 (Asia/Calcutta)

## Read this first

The repository contains a large **uncommitted** database-per-company tenancy and Super Admin security change. Do not reset, discard, or overwrite the working tree. Treat every modified and untracked file as user work until reviewed.

Workspace: `C:\wamp64\www\crm`

This file is the portable task memory. There is no project-accessible file containing the prior assistant's private conversation memory; use this handoff, the working-tree diff, and `docs/TENANCY.md` as the source of continuity.

## Intended outcome

The implementation is moving the Laravel CRM toward:

- one central/master database for tenant registry, identities, credentials, roles/permissions, subscriptions, tokens, notifications, and platform audit data;
- one MySQL database per company for CRM business data;
- a separate Super Admin portal and route boundary;
- exactly one authoritative company Admin (`tenants.admin_user_id`);
- tenant-scoped custom roles and permissions;
- lifecycle enforcement for pending, rejected, inactive, expired, and unprovisioned tenants;
- operational commands for provisioning, schema migration, status, and isolation verification.

See `docs/TENANCY.md` for the current architecture and operational intent.

## Current working-tree state

At handoff, `git status --short` reported these modified files:

```text
app/Console/Commands/ProvisionTenantDatabases.php
app/Console/Commands/SendTaskReminders.php
app/Http/Controllers/Admin/MasterDataController.php
app/Http/Controllers/Admin/PlatformDashboardController.php
app/Http/Controllers/Admin/PlatformUserController.php
app/Http/Controllers/Admin/TenantController.php
app/Http/Controllers/Admin/UserController.php
app/Http/Controllers/AuthController.php
app/Http/Controllers/RegistrationController.php
app/Http/Controllers/SuperAdminAuthController.php
app/Http/Middleware/ActivateTenantDatabase.php
app/Http/Middleware/EnsureActiveApiUser.php
app/Http/Middleware/EnsureSingleSession.php
app/Models/PlatformAuditLog.php
app/Models/Tenant.php
app/Models/User.php
app/Support/TenantContext.php
app/Tenancy/TenantDatabaseProvisioner.php
config/permission.php
database/seeders/RolePermissionSeeder.php
resources/views/include/sidebar.blade.php
resources/views/platform/dashboard.blade.php
resources/views/platform/users.blade.php
resources/views/tenants/index.blade.php
routes/app.php
tests/Feature/SuperAdminPortalTest.php
tests/Feature/TenantManagementTest.php
tests/TestCase.php
```

Untracked additions:

```text
app/Console/Commands/MigrateTenantSchemas.php
app/Console/Commands/TenantStatus.php
app/Console/Commands/VerifyTenantIsolation.php
app/Http/Controllers/Admin/RoleController.php
app/Services/CompanyAdminManager.php
app/Services/PlatformAuditLogger.php
app/Support/PermissionTeam.php
database/migrations/2026_08_11_100013_enable_tenant_scoped_roles.php
database/migrations/tenant/README.md
docs/TENANCY.md
resources/views/roles/index.blade.php
tests/Feature/TenantLifecycleTest.php
tests/Feature/TenantRoleManagementTest.php
```

Run `git status --short` again because the user or another agent may have changed the tree after this handoff.

## Verification performed

Command attempted:

```text
php artisan test --testsuite=Feature --filter="(SuperAdminPortalTest|TenantManagementTest|TenantLifecycleTest|TenantRoleManagementTest)"
```

It timed out after 60 seconds while reporting failures. A narrower run provided the actual blocker:

```text
php artisan test tests/Feature/SuperAdminPortalTest.php --stop-on-failure
```

Result: 2 tests failed with 0 assertions because PDO could not connect to MySQL:

```text
SQLSTATE[HY000] [2002] No connection could be made because the target machine actively refused it
```

There was also a non-fatal Xdebug warning that `c:/wamp64/logs/xdebug.log` could not be opened. Therefore, the application behavior is **not yet verified**. Do not interpret this run as evidence that the assertions or tenancy code are wrong; restore/start the configured test database first.

## Recommended continuation plan

1. Read `docs/TENANCY.md`, then inspect all diffs with `git diff` and all untracked files. Confirm that central versus tenant-table ownership matches the actual models and existing migrations.
2. Inspect `.env.testing`, `phpunit.xml`, and Laravel database configuration. Start the intended WAMP MySQL service or point tests at a safe disposable test database. Never run destructive tenancy tests against production/user data.
3. Run syntax and route checks before database tests:
   - `php artisan route:list`
   - PHP lint on changed/new PHP files
   - `php artisan config:clear` if stale cached configuration is suspected
4. Run the four targeted feature test classes individually, then together:
   - `tests/Feature/SuperAdminPortalTest.php`
   - `tests/Feature/TenantManagementTest.php`
   - `tests/Feature/TenantLifecycleTest.php`
   - `tests/Feature/TenantRoleManagementTest.php`
5. Fix failures without weakening isolation/security assertions. Pay special attention to:
   - Super Admin routes remaining outside tenant middleware and legacy platform URLs being unavailable;
   - `tenants.admin_user_id` enforcing exactly one designated Admin;
   - registration creating one pending tenant plus one inactive Admin;
   - approval/expiry/status/provisioning checks across browser login, active sessions, and API middleware;
   - Spatie permission team/tenant context being set and cleared correctly between requests/tests;
   - tenant database connections never leaking across queued commands or requests;
   - provisioning being idempotent and database identifiers being strictly validated;
   - audit events for privileged lifecycle and impersonation actions;
   - tenant migrations applying only to provisioned tenant databases.
6. Run the full test suite after targeted tests pass. Record any pre-existing unrelated failures separately.
7. On a disposable local setup, exercise the documented commands from `docs/TENANCY.md`, especially isolation verification. Confirm its temporary database cleanup runs even after an exception.
8. Review UI authorization for the new role screen, sidebar links, platform users, dashboard, and tenant listing.
9. Update `docs/TENANCY.md` if implementation details change. Only commit after the user approves the reviewed diff and verification status.

## Useful first commands

```text
cd C:\wamp64\www\crm
git status --short
git diff --stat
git diff
Get-Content docs\TENANCY.md
Get-Content docs\HANDOFF_FOR_CLAUDE.md
```

## Important cautions

- Do not use `git reset --hard`, checkout-overwrite, or clean untracked files.
- Do not delete tenant databases. Deactivation/rejection is not authorization for data deletion.
- The isolation command intentionally creates and drops strictly named temporary databases; inspect its safeguards and use only a disposable development MySQL instance.
- The migration `2026_08_11_100013_enable_tenant_scoped_roles.php` is intentionally irreversible and throws in `down()`. Ensure backups and deployment procedure are acceptable before applying it anywhere important.
- Recent commits before this uncommitted work were `fd14682`, `24253e5`, `a59bb49`, `842a813`, and `b9aff24`; do not assume the current changes belong to any commit.

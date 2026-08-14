# Database-per-company tenancy

## Entry points

- CRM login: `http://localhost/crm/public/`
- Company signup: `http://localhost/crm/public/register`
- Super Admin login: `http://localhost/crm/public/superadmin/login`
- Super Admin companies: `http://localhost/crm/public/superadmin/companies`

Super Admin routes are defined only in `routes/superadmin.php`. They are not part of the tenant CRM route group and never open a tenant database directly. Support access requires audited impersonation of a tenant user.

## Data boundaries

The configured master database (`crm`) is the control plane. It stores:

- tenant/company registry and database connection metadata;
- all login identities and hashed passwords;
- roles, permissions, API tokens, and notifications;
- approval, plan, expiry, health, and provisioning state;
- Super Admin lifecycle and impersonation audit events.

Each company has its own MySQL database. It stores only CRM business records such as leads, companies, contacts, deals, pipelines, orders, payments, tasks, attendance, and tenant audit logs. Business models resolve the active `tenant` connection through `TenantConnectionManager`.

## Registration lifecycle

1. Public registration creates one pending tenant and exactly one inactive Admin in the master database.
2. A fresh tenant database is provisioned immediately.
3. The signup appears in Super Admin under Companies & Signups.
4. Approval activates only the designated Admin. A trial starts on approval and lasts 14 days unless Super Admin sets another expiry.
5. Inactive, rejected, expired, or unprovisioned tenants are blocked at login, active sessions, and API middleware.

Admin is an invariant: `tenants.admin_user_id` is authoritative. Every other account is a counted user and receives a tenant-created custom role.

## Operations

```text
php artisan crm:tenant-status --health
php artisan crm:provision-tenants --tenant=ID
php artisan crm:migrate-tenant-schemas
php artisan crm:verify-tenant-isolation
```

- `tenant-status` reports approval, status, expiry, users, administrator, provisioning, health, and database name. It fails when the exactly-one-admin invariant is broken.
- `provision-tenants` creates or safely completes an isolated database. Use `--copy-existing` only for a controlled first migration from the legacy master copy.
- `migrate-tenant-schemas` applies migrations from `database/migrations/tenant` to every ready tenant database.
- `verify-tenant-isolation` creates two strictly named temporary databases, proves identical primary keys retain independent values, and removes both databases in a `finally` block.

## Deployment rules

- Keep `TENANCY_MODE=database` outside the legacy compatibility test suite.
- Back up the master database and every tenant database independently.
- Put central schema changes in `database/migrations`.
- Put CRM business-table changes in `database/migrations/tenant`, then run both the central migration and tenant migration command during deployment.
- Never delete a tenant database as part of deactivation or rejection. Retention/deletion requires a separate explicit operational policy.

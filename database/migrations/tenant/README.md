# Tenant database migrations

Place migrations that change CRM business tables in this directory. Run them for every provisioned company with:

```text
php artisan tenants:migrate
```

Central identity, subscription, and tenant-registry migrations remain in `database/migrations` and run with the normal Laravel migrate command.

`2026_08_15_100001` through `2026_08_15_100006` create the full initial business schema (leads, companies, contacts, deals, pipelines, orders, etc.), split into dependency tiers so foreign keys between tenant tables resolve in the right order. Column definitions were captured verbatim from the central database as the source of truth. Foreign keys to `tenants`/`users` are intentionally omitted since those tables only exist centrally, not per tenant — only intra-tenant relationships (e.g. `leads.company_id -> companies.id`) are constrained. Every `up()` is guarded with `Schema::hasTable()` so re-running migrations against an already-provisioned tenant (e.g. via "Re-provision safely" or `tenants:migrate`) is a safe no-op.

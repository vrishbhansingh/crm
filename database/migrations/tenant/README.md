# Tenant database migrations

Place migrations that change CRM business tables in this directory. Run them for every provisioned company with:

```text
php artisan crm:migrate-tenant-schemas
```

Central identity, subscription, and tenant-registry migrations remain in `database/migrations` and run with the normal Laravel migrate command.

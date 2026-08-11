<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `company_details` ENGINE=InnoDB');
        DB::statement('ALTER TABLE `payment_details` ENGINE=InnoDB');

        DB::statement('ALTER TABLE `company_details` ADD CONSTRAINT `company_details_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL');
        DB::statement('ALTER TABLE `payment_details` ADD CONSTRAINT `payment_details_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL');
        DB::statement('ALTER TABLE `payment_details` ADD CONSTRAINT `payment_details_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `payment_details` DROP FOREIGN KEY `payment_details_order_id_foreign`');
        DB::statement('ALTER TABLE `payment_details` DROP FOREIGN KEY `payment_details_tenant_id_foreign`');
        DB::statement('ALTER TABLE `company_details` DROP FOREIGN KEY `company_details_tenant_id_foreign`');

        DB::statement('ALTER TABLE `payment_details` ENGINE=MyISAM');
        DB::statement('ALTER TABLE `company_details` ENGINE=MyISAM');
    }
};

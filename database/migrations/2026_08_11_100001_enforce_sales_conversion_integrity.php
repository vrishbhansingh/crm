<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('leads')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('deals')
                    ->whereColumn('deals.lead_id', 'leads.id');
            })
            ->update([
                'is_converted' => 'No',
                'converted_at' => null,
                'conversion_value' => null,
            ]);

        DB::table('leads')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('deals')
                    ->whereColumn('deals.lead_id', 'leads.id');
            })
            ->update(['is_converted' => 'Yes']);

        DB::statement("ALTER TABLE `leads` MODIFY `is_converted` enum('Yes','No') DEFAULT 'No'");

        Schema::table('deals', function (Blueprint $table) {
            $table->unique('lead_id', 'deals_lead_id_unique');
            $table->unique('order_id', 'deals_order_id_unique');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unique('order_number', 'orders_order_number_unique');
            $table->unique('invoice_id', 'orders_invoice_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_order_number_unique');
            $table->dropUnique('orders_invoice_id_unique');
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->dropUnique('deals_lead_id_unique');
            $table->dropUnique('deals_order_id_unique');
        });

        DB::statement("ALTER TABLE `leads` MODIFY `is_converted` enum('Yes','No') DEFAULT 'Yes'");
    }
};

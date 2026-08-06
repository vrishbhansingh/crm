<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_type_id')->constrained('master_types')->cascadeOnDelete();
            // null = global default value visible to every tenant;
            // set = a tenant's own custom addition on top of the globals.
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('code');
            $table->string('label');
            $table->string('color')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['master_type_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_values');
    }
};

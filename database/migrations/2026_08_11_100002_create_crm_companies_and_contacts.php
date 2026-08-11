<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('website')->nullable();
            $table->string('industry')->nullable();
            $table->string('company_size', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('gst_number', 50)->nullable();
            $table->string('pan_number', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('pincode', 20)->nullable();
            $table->string('status', 30)->default('prospect');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'name']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'owner_id']);
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('alternate_phone', 50)->nullable();
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->string('source', 50)->nullable();
            $table->string('status', 30)->default('active');
            $table->text('notes')->nullable();
            $table->unsignedInteger('legacy_customer_contact_id')->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'company_id']);
            $table->index(['tenant_id', 'owner_id']);
            $table->index(['tenant_id', 'email']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('tenant_id')->constrained('companies')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->after('company_id')->constrained('contacts')->nullOnDelete();
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('lead_id')->constrained('companies')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->after('company_id')->constrained('contacts')->nullOnDelete();
        });

        $this->migrateLegacyData();
    }

    private function migrateLegacyData(): void
    {
        $companyIds = [];

        DB::table('leads')->orderBy('id')->chunkById(100, function ($leads) use (&$companyIds) {
            foreach ($leads as $lead) {
                $name = trim((string) $lead->company_name);
                if ($name === '') {
                    continue;
                }

                $cacheKey = ($lead->tenant_id ?? 'platform').'|'.mb_strtolower($name);
                if (! isset($companyIds[$cacheKey])) {
                    $query = DB::table('companies')->where('name', $name);
                    $lead->tenant_id === null
                        ? $query->whereNull('tenant_id')
                        : $query->where('tenant_id', $lead->tenant_id);

                    $companyId = $query->value('id');
                    if (! $companyId) {
                        $companyId = DB::table('companies')->insertGetId([
                            'tenant_id' => $lead->tenant_id,
                            'owner_id' => $lead->assigned_to,
                            'name' => $name,
                            'gst_number' => $lead->gst_no,
                            'city' => $lead->city,
                            'state' => $lead->state,
                            'country' => $lead->country,
                            'status' => 'prospect',
                            'created_at' => $lead->created_at,
                            'updated_at' => $lead->updated_at,
                        ]);
                    }
                    $companyIds[$cacheKey] = $companyId;
                }

                DB::table('leads')->where('id', $lead->id)->update([
                    'company_id' => $companyIds[$cacheKey],
                ]);
            }
        });

        if (Schema::hasTable('customer_contact')) {
            DB::table('customer_contact')->orderBy('id')->chunkById(100, function ($legacyContacts) {
                foreach ($legacyContacts as $legacy) {
                    $lead = DB::table('leads')->where('id', $legacy->lead_id)->first();
                    $contactId = DB::table('contacts')->insertGetId([
                        'tenant_id' => $legacy->tenant_id ?? $lead?->tenant_id,
                        'company_id' => $lead?->company_id,
                        'owner_id' => $lead?->assigned_to,
                        'name' => $legacy->name,
                        'email' => $legacy->email ?: null,
                        'phone' => $legacy->phone ?: null,
                        'designation' => $legacy->designation ?: null,
                        'city' => $legacy->city ?: null,
                        'is_primary' => true,
                        'source' => 'legacy_lead',
                        'status' => strtolower($legacy->status ?? 'Active'),
                        'legacy_customer_contact_id' => $legacy->id,
                        'created_at' => $legacy->created_at,
                        'updated_at' => $legacy->updated_at,
                    ]);

                    if ($lead && ! $lead->contact_id) {
                        DB::table('leads')->where('id', $lead->id)->update(['contact_id' => $contactId]);
                    }
                }
            });
        }

        DB::statement('UPDATE deals d INNER JOIN leads l ON l.id = d.lead_id SET d.company_id = l.company_id, d.contact_id = l.contact_id');
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contact_id');
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contact_id');
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::dropIfExists('contacts');
        Schema::dropIfExists('companies');
    }
};

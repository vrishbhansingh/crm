<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Order;
use App\Models\PaymentDetails;
use App\Models\PipelineStage;
use App\Models\Task;
use App\Support\TenantContext;
use App\Tenancy\TenantConnectionManager;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Populates tenant 5 ("Wonder Technologies" — the only tenant with real
 * production data, per the crm.sql / crm_tenant_5_wonder_technologies_*.sql
 * dumps supplied 2026-09-14) with enough realistic demo records to evaluate
 * the dashboard/leads/deals UI against real data volume, instead of the
 * single starter lead ("Jai yadav") it shipped with.
 *
 * Purely additive — never touches the existing lead/company/contact/
 * pipeline/stage rows, and lets auto-increment pick fresh ids so nothing
 * collides with what's already there. Reuses the tenant's own existing
 * pipeline (id 1, "Sales Pipeline") and its five stages rather than
 * creating a second pipeline.
 *
 * Run with: php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    private const TENANT_ID = 5;

    private const USER_ID = 5;

    public function run(): void
    {
        $connectionManager = app(TenantConnectionManager::class);
        $connectionManager->activate(self::TENANT_ID);
        $connectionName = $connectionManager->connectionName();
        TenantContext::set(self::TENANT_ID);

        // Wrapped in a transaction so a failure partway through (like the
        // Collection/array type mismatch this hit on the first run) can't
        // leave half-seeded rows behind — either all of this commits, or
        // none of it does, and the seeder is safe to just re-run.
        DB::connection($connectionName)->transaction(function () {
            $companies = $this->seedCompanies();
            $contacts = $this->seedContacts($companies);
            $this->seedLeads($contacts);
            $stageIds = PipelineStage::where('pipeline_id', 1)->pluck('id', 'name')->all();
            $deals = $this->seedDeals($stageIds, $companies, $contacts);
            $this->seedOrdersAndPayments();
            $this->seedTasks($deals);
        });

        $this->command?->info('Demo data seeded into tenant 5 (Wonder Technologies).');
    }

    private function seedCompanies(): array
    {
        $rows = [
            ['name' => 'Skyline Retail Pvt Ltd', 'industry' => 'Retail', 'city' => 'Mumbai', 'state' => 'Maharashtra'],
            ['name' => 'Harbor Logistics', 'industry' => 'Logistics', 'city' => 'Chennai', 'state' => 'Tamil Nadu'],
            ['name' => 'Ironclad Security Systems', 'industry' => 'Security', 'city' => 'Pune', 'state' => 'Maharashtra'],
            ['name' => 'Nimbus Cloud Solutions', 'industry' => 'IT Services', 'city' => 'Bengaluru', 'state' => 'Karnataka'],
        ];

        return array_map(fn (array $row) => Company::create([
            'tenant_id' => self::TENANT_ID,
            'owner_id' => self::USER_ID,
            'name' => $row['name'],
            'industry' => $row['industry'],
            'city' => $row['city'],
            'state' => $row['state'],
            'country' => 'India',
            'status' => 'prospect',
        ])->id, $rows);
    }

    private function seedContacts(array $companyIds): array
    {
        $rows = [
            ['name' => 'Ananya Sharma', 'designation' => 'Procurement Head', 'email' => 'ananya.sharma@example.com', 'phone' => '9811000101'],
            ['name' => 'Rohit Malhotra', 'designation' => 'Operations Manager', 'email' => 'rohit.malhotra@example.com', 'phone' => '9811000102'],
            ['name' => 'Kavita Iyer', 'designation' => 'CTO', 'email' => 'kavita.iyer@example.com', 'phone' => '9811000103'],
            ['name' => 'Sameer Khan', 'designation' => 'Facilities Director', 'email' => 'sameer.khan@example.com', 'phone' => '9811000104'],
        ];

        $contactIds = [];
        foreach ($rows as $i => $row) {
            $contactIds[] = Contact::create([
                'tenant_id' => self::TENANT_ID,
                'company_id' => $companyIds[$i],
                'owner_id' => self::USER_ID,
                'name' => $row['name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'designation' => $row['designation'],
                'source' => 'lead',
                'status' => 'active',
                'is_primary' => true,
            ])->id;
        }

        return $contactIds;
    }

    private function seedLeads(array $contactIds): void
    {
        $leads = [
            ['name' => 'Ananya Sharma', 'source' => 'website', 'status' => 'new', 'priority' => 'high', 'days_ago' => 0],
            ['name' => 'Rohit Malhotra', 'source' => 'referral', 'status' => 'contacted', 'priority' => 'medium', 'days_ago' => 0],
            ['name' => 'Kavita Iyer', 'source' => 'social_media', 'status' => 'qualified', 'priority' => 'high', 'days_ago' => 1],
            ['name' => 'Sameer Khan', 'source' => 'cold_call', 'status' => 'new', 'priority' => 'low', 'days_ago' => 1],
            ['name' => 'Neha Kapoor', 'source' => 'website', 'status' => 'contacted', 'priority' => 'medium', 'days_ago' => 3],
            ['name' => 'Vikram Rathi', 'source' => 'email_campaign', 'status' => 'new', 'priority' => 'medium', 'days_ago' => 4],
            ['name' => 'Priyanka Desai', 'source' => 'referral', 'status' => 'qualified', 'priority' => 'high', 'days_ago' => 6],
            ['name' => 'Arjun Nair', 'source' => 'website', 'status' => 'warm', 'priority' => 'medium', 'days_ago' => 8],
            ['name' => 'Meera Pillai', 'source' => 'social_media', 'status' => 'hot', 'priority' => 'high', 'days_ago' => 9],
            ['name' => 'Tarun Bhatia', 'source' => 'cold_call', 'status' => 'not_interested', 'priority' => 'low', 'days_ago' => 11],
            ['name' => 'Divya Menon', 'source' => 'website', 'status' => 'new', 'priority' => 'medium', 'days_ago' => 13],
            ['name' => 'Karan Chawla', 'source' => 'referral', 'status' => 'contacted', 'priority' => 'high', 'days_ago' => 14],
        ];

        foreach ($leads as $i => $row) {
            $createdAt = Carbon::now()->subDays($row['days_ago'])->setTime(10, 30);

            Lead::create([
                'tenant_id' => self::TENANT_ID,
                'contact_id' => $contactIds[$i % count($contactIds)] ?? null,
                'lead_type' => 'new',
                'lead_source' => $row['source'],
                'name' => $row['name'],
                'phone' => '98110002' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'email' => strtolower(str_replace(' ', '.', $row['name'])).'@example.com',
                'city' => 'Delhi',
                'country' => 'India',
                'lead_status' => $row['status'],
                'priority' => $row['priority'],
                'assigned_to' => self::USER_ID,
                'assigned_by' => self::USER_ID,
                'assigned_at' => $createdAt,
                'status' => 'Active',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    private function seedDeals(array $stageIds, array $companyIds, array $contactIds): array
    {
        $deals = [
            ['name' => 'Skyline Retail — POS rollout', 'stage' => 'Prospecting', 'amount' => 480000, 'status' => 'open', 'company' => 0],
            ['name' => 'Harbor Logistics — fleet tracking', 'stage' => 'Prospecting', 'amount' => 265000, 'status' => 'open', 'company' => 1],
            ['name' => 'Ironclad — access control upgrade', 'stage' => 'Prospecting', 'amount' => 190000, 'status' => 'open', 'company' => 2],
            ['name' => 'Nimbus Cloud — migration project', 'stage' => 'Proposal', 'amount' => 620000, 'status' => 'open', 'company' => 3],
            ['name' => 'Skyline Retail — loyalty platform', 'stage' => 'Proposal', 'amount' => 340000, 'status' => 'open', 'company' => 0],
            ['name' => 'Harbor Logistics — warehouse suite', 'stage' => 'Negotiation', 'amount' => 410000, 'status' => 'open', 'company' => 1],
            ['name' => 'Ironclad — annual AMC renewal', 'stage' => 'Won', 'amount' => 150000, 'status' => 'won', 'company' => 2],
            ['name' => 'Nimbus Cloud — support contract', 'stage' => 'Won', 'amount' => 275000, 'status' => 'won', 'company' => 3],
            ['name' => 'Skyline Retail — kiosk pilot', 'stage' => 'Lost', 'amount' => 95000, 'status' => 'lost', 'company' => 0],
        ];

        $dealIds = [];
        foreach ($deals as $i => $row) {
            $createdAt = Carbon::now()->subDays(20 - $i * 2);
            $closedAt = $row['status'] !== 'open' ? $createdAt->copy()->addDays(5) : null;

            $dealIds[] = Deal::create([
                'tenant_id' => self::TENANT_ID,
                'pipeline_id' => 1,
                'stage_id' => $stageIds[$row['stage']],
                'company_id' => $companyIds[$row['company']],
                'contact_id' => $contactIds[$row['company']],
                'owner_id' => self::USER_ID,
                'created_by' => self::USER_ID,
                'name' => $row['name'],
                'amount' => $row['amount'],
                'currency' => 'INR',
                'expected_close_date' => Carbon::now()->addDays(7 + $i * 3)->toDateString(),
                'closed_at' => $closedAt,
                'status' => $row['status'],
                'created_at' => $createdAt,
                'updated_at' => $closedAt ?? $createdAt,
            ])->id;
        }

        return $dealIds;
    }

    /**
     * Six months of orders + payments so the dashboard's Revenue Overview
     * chart has real bars instead of the "No revenue recorded yet." empty
     * state. Tied to the user id rather than any specific deal — Order has
     * no deal_id column (orders are created from a Won deal via
     * DealController::createOrderFromDeal in the real flow, but this
     * seeder only needs plausible historical revenue, not the full
     * deal-to-order lifecycle).
     */
    private function seedOrdersAndPayments(): void
    {
        for ($m = 5; $m >= 0; $m--) {
            $invoiceDate = Carbon::now()->subMonths($m)->startOfMonth()->addDays(6);
            $net = 90000 + ($m * 18000);
            $paid = (int) round($net * 0.75);

            $order = Order::create([
                'tenant_id' => self::TENANT_ID,
                'user_id' => self::USER_ID,
                'order_number' => 'WT-'.$invoiceDate->format('Ym').'-'.($m + 1),
                'invoice_date' => $invoiceDate,
                'net_amount' => $net,
                'paid_amount' => $paid,
                'due_amount' => $net - $paid,
                'order_status' => 'in_progress',
                'currency' => 'INR',
                'status' => 'Active',
                'created_at' => $invoiceDate,
                'updated_at' => $invoiceDate,
            ]);

            PaymentDetails::create([
                'tenant_id' => self::TENANT_ID,
                'order_id' => $order->id,
                'payment_mode' => 'bank_transfer',
                'payment_date' => $invoiceDate->copy()->addDays(3),
                'paid_amount' => $paid,
                'status' => 'Active',
                'created_at' => $invoiceDate->copy()->addDays(3),
                'updated_at' => $invoiceDate->copy()->addDays(3),
            ]);
        }
    }

    private function seedTasks(array $dealIds): void
    {
        $tasks = [
            ['title' => 'Follow up on Nimbus Cloud proposal', 'due_in_days' => 0, 'priority' => 'high', 'deal' => 3],
            ['title' => 'Send revised quote to Harbor Logistics', 'due_in_days' => 0, 'priority' => 'medium', 'deal' => 1],
            ['title' => 'Schedule demo for Skyline Retail', 'due_in_days' => 1, 'priority' => 'medium', 'deal' => 4],
            ['title' => 'Confirm Ironclad AMC renewal paperwork', 'due_in_days' => 2, 'priority' => 'low', 'deal' => 6],
            ['title' => 'Check in with Nimbus Cloud after go-live', 'due_in_days' => -1, 'priority' => 'medium', 'deal' => 7],
        ];

        foreach ($tasks as $row) {
            $dueAt = Carbon::now()->addDays($row['due_in_days'])->setTime(15, 0);

            Task::create([
                'tenant_id' => self::TENANT_ID,
                'assigned_to' => self::USER_ID,
                'created_by' => self::USER_ID,
                'related_type' => 'deal',
                'related_id' => $dealIds[$row['deal']],
                'title' => $row['title'],
                'priority' => $row['priority'],
                'status' => $row['due_in_days'] < 0 ? 'completed' : 'todo',
                'due_at' => $dueAt,
                'completed_at' => $row['due_in_days'] < 0 ? $dueAt->copy()->addHour() : null,
            ]);
        }
    }
}

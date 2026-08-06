<?php

namespace Database\Seeders;

use App\Models\MasterType;
use App\Models\MasterValue;
use Illuminate\Database\Seeder;

/**
 * Seeds the master types/values that are genuinely live in the app today
 * (previously hardcoded as MySQL ENUMs). All global (tenant_id null) so
 * every tenant starts with the same sensible defaults and can add their
 * own values later via the Master Management admin screen.
 *
 * `linkedIn` keeps its original casing (not `linkedin`) to match the exact
 * string already stored on existing lead rows from the old ENUM column.
 */
class MasterDataSeeder extends Seeder
{
    private array $types = [
        'lead_type' => [
            'name' => 'Lead Type',
            'values' => [
                'cold' => 'Cold',
                'warm' => 'Warm',
                'hot' => 'Hot',
                'inquiry' => 'Inquiry',
                'existing_customer' => 'Existing Customer',
            ],
        ],
        'lead_source' => [
            'name' => 'Lead Source',
            'values' => [
                'website' => 'Website',
                'facebook_ads' => 'Facebook Ads',
                'google_ads' => 'Google Ads',
                'referral' => 'Referral',
                'cold_call' => 'Cold Call',
                'email' => 'Email',
                'partner' => 'Partner',
                'linkedIn' => 'LinkedIn',
                'other' => 'Other',
            ],
        ],
        'lead_status' => [
            'name' => 'Lead Status',
            'values' => [
                'new' => 'New',
                'contacted' => 'Contacted',
                'interested' => 'Interested',
                'follow_up' => 'Follow Up',
                'not_interested' => 'Not Interested',
                'converted' => 'Converted',
                'closed' => 'Closed',
            ],
        ],
        'lead_priority' => [
            'name' => 'Lead Priority',
            'values' => [
                'high' => 'High',
                'medium' => 'Medium',
                'low' => 'Low',
            ],
        ],
        'order_status' => [
            'name' => 'Order Status',
            'values' => [
                'new' => 'New',
                'approved' => 'Approved',
                'in_progress' => 'In Progress',
                'on_hold' => 'On Hold',
                'delivered' => 'Delivered',
                'closed' => 'Closed',
                'cancelled' => 'Cancelled',
            ],
        ],
        'payment_terms' => [
            'name' => 'Payment Terms',
            'values' => [
                'advance' => 'Advance',
                'partial_advance' => 'Partial Advance',
                'on_delivery' => 'On Delivery',
                'net_15' => 'Net 15',
                'net_30' => 'Net 30',
            ],
        ],
        'payment_status' => [
            'name' => 'Payment Status',
            'values' => [
                'pending' => 'Pending',
                'partial' => 'Partial',
                'paid' => 'Paid',
            ],
        ],
        'currency' => [
            'name' => 'Currency',
            'values' => [
                'INR' => 'Indian Rupee (INR)',
                'USD' => 'US Dollar (USD)',
                'EUR' => 'Euro (EUR)',
            ],
        ],
        'project_priority' => [
            'name' => 'Project Priority',
            'values' => [
                'low' => 'Low',
                'medium' => 'Medium',
                'high' => 'High',
                'urgent' => 'Urgent',
            ],
        ],
        'payment_mode' => [
            'name' => 'Payment Mode',
            'values' => [
                'cash' => 'Cash',
                'upi' => 'UPI',
                'bank_transfer' => 'Bank Transfer',
                'cheque' => 'Cheque',
                'card' => 'Card',
            ],
        ],
    ];

    public function run(): void
    {
        foreach ($this->types as $typeCode => $definition) {
            $type = MasterType::firstOrCreate(
                ['code' => $typeCode],
                ['name' => $definition['name'], 'is_active' => true]
            );

            $sortOrder = 0;
            foreach ($definition['values'] as $valueCode => $label) {
                MasterValue::firstOrCreate(
                    ['master_type_id' => $type->id, 'tenant_id' => null, 'code' => $valueCode],
                    ['label' => $label, 'sort_order' => $sortOrder]
                );
                $sortOrder++;
            }
        }
    }
}

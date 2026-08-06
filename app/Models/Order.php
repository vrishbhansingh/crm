<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory, BelongsToTenant;
    protected $table = 'orders';
    protected $fillable = [
        'tenant_id',
        'order_number',
        'lead_id',
        'invoice_id',
        'invoice_date',
        'project_id',
        'user_id',
        'sub_total',
        'discount',
        'gst',
        'total_amount',
        'order_status',
        'payment_terms',
        'payment_status',
        'currency',
        'due_amount',
        'net_amount',
        'paid_amount',
        'status',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function project()
    {
        return $this->belongsTo(ProjectInfo::class, 'project_id');
    }
}

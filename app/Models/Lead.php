<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;
    protected $table = 'leads';
    protected $fillable = [
        'lead_type',
        'lead_source',
        'name',
        'phone',
        'alternate_phone',
        'email',
        'city',
        'state',
        'country',
        'products',
        'service',
        'budget',
        'requirement',
        'lead_status',
        'priority',
        'follow_up_date',
        'follow_up_time',
        'follow_up_note',
        'assigned_to',
        'assigned_by',
        'assigned_at',
        'remarks',
        'internal_note',
        'is_converted',
        'converted_at',
        'conversion_value',
        'status_reason',
        'last_contacted_at',
        'status'
    ];
}

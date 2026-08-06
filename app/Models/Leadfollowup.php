<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leadfollowup extends Model
{
    use HasFactory, BelongsToTenant;
    protected $table = 'lead_follow_up';
    protected $fillable = [
        'tenant_id',
        'lead_id',
        'user_id',
        'lead_response',
        'follow_up_date',
        'follow_up_time',
        'call_status',
        'call_note',
        'status',
    ];
}

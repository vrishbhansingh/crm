<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectInfo extends Model
{
    use HasFactory, BelongsToTenant;
    protected $table = 'project_info';
    protected $fillable = [
        'tenant_id',
        'project_name',
        'tech_stack',
        'expected_start_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'priority',
        'description',
        'status',
    ];
}

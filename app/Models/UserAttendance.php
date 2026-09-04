<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAttendance extends Model
{
    use HasFactory, BelongsToTenant;
    protected $table = 'user_attendance';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'date',
        'check_out',
        'status',
    ];
}

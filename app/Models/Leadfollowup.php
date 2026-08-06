<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leadfollowup extends Model
{
    use HasFactory, BelongsToTenant;
    protected $table = 'lead_follow_up';
}

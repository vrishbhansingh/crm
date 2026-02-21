<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;
    protected $table = 'admin_details';
    protected $fillable = [
        'username',
        'password',
        'last_login',
        'status',
    ];
}

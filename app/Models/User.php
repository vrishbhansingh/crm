<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'user_list';

    protected $fillable = [
        'email',
        'password',
        'backup',
        'last_login',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Employee extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'role',
        'hourly_wage',
        'transportation_fee',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}

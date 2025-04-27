<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'lastName',
        'identification',
        'department_id',
        'city_id',
        'phone',
        'email',
        'authorization',
        'win'
    ];

    protected $casts = [
        'authorization' => 'boolean',
    ];
}

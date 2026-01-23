<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    public $timestamps = false;
    protected $table = 'admin';

    protected $fillable = [
        'username',
        'password',
        'email',
        'uid',
        'name',
        'role',
        'status',
        'created_at',
    ];

    protected $hidden = [
        'password',
    ];
}

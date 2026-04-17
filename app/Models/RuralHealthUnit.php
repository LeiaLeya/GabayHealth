<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RuralHealthUnit extends Model
{
    protected $fillable = [
        'name',
        'city',
        'status',
    ];
}

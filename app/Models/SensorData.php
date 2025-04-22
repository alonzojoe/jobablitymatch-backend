<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_name',
        'maximum_load',
        'current_load',
        'deflection',
        'status',
        'user_id',
    ];
}

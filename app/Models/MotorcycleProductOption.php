<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotorcycleProductOption extends Model
{
    use HasFactory;

    public const TYPES = [
        'helmet_type',
        'helmet_size',
        'color',
        'accessory_type',
        'part_type',
    ];

    protected $fillable = [
        'type',
        'name',
        'status',
    ];
}

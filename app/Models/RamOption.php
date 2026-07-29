<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RamOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'status',
    ];

    protected $casts = [
        'value' => 'integer',
    ];
}

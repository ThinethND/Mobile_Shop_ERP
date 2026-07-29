<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoeColor extends Model
{
    use HasFactory;

    protected $table = 'shoe_colors';

    protected $fillable = [
        'name',
        'hex_code',
        'status',
    ];
}
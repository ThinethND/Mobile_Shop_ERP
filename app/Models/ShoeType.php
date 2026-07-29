<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoeType extends Model
{
    use HasFactory;

    protected $table = 'shoe_types';

    protected $fillable = [
        'name',
        'status',
    ];
}
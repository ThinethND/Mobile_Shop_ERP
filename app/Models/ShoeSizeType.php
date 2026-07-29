<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoeSizeType extends Model
{
    use HasFactory;

    protected $table = 'shoe_size_types';

    protected $fillable = [
        'code',
        'name',
        'status',
    ];
}
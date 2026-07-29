<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoeMaterial extends Model
{
    use HasFactory;

    protected $table = 'shoe_materials';

    protected $fillable = [
        'name',
        'status',
    ];
}
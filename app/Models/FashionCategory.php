<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FashionCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function productTypes()
    {
        return $this->hasMany(FashionProductType::class, 'category_id');
    }

    public function products()
    {
        return $this->hasMany(FashionProduct::class, 'category_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CosmeticCategory extends Model
{
    use HasFactory;

    protected $table = 'cosmetic_categories';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function productTypes()
    {
        return $this->hasMany(CosmeticProductType::class, 'cosmetic_category_id');
    }

    public function products()
    {
        return $this->hasMany(CosmeticProduct::class, 'category_id');
    }
}


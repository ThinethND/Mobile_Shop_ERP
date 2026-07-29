<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CosmeticProductType extends Model
{
    use HasFactory;

    protected $table = 'cosmetic_product_types';

    protected $fillable = [
        'cosmetic_category_id',
        'name',
    ];

    public function category()
    {
        return $this->belongsTo(CosmeticCategory::class, 'cosmetic_category_id');
    }

    public function products()
    {
        return $this->hasMany(CosmeticProduct::class, 'product_type_id');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FashionProductType extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(FashionCategory::class, 'category_id');
    }

    public function products()
    {
        return $this->hasMany(FashionProduct::class, 'product_type_id');
    }
}

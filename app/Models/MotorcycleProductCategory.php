<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotorcycleProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_category_id',
        'description',
        'status',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_category_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_category_id');
    }

    public function products()
    {
        return $this->hasMany(MotorcycleProduct::class, 'category_id');
    }
}

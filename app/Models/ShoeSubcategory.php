<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ShoeSubcategory extends Model
{
    use HasFactory;

    protected $table = 'shoe_subcategories';

    protected $fillable = [
    'category_id',
    'name',
    'image_path',
    'status',
];

    public function category()
    {
        return $this->belongsTo(ShoeCategory::class, 'category_id');
    }

    protected $appends = [
    'image_url',
];

public function getImageUrlAttribute(): ?string
{
    if (!$this->image_path) {
        return null;
    }

    $path = str_replace('\\', '/', $this->image_path);
    $path = preg_replace('#^public/#', '', $path);
    $path = ltrim($path, '/');

    return asset('storage/' . $path);
}
}
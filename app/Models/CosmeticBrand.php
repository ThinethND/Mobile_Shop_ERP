<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CosmeticBrand extends Model
{
    use HasFactory;

    protected $table = 'cosmetic_brands';

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'status',
    ];

    protected $appends = [
        'logo_url',
    ];

    public function products()
    {
        return $this->hasMany(CosmeticProduct::class, 'brand_id');
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CosmeticCountryOfOrigin extends Model
{
    use HasFactory;

    protected $table = 'cosmetic_countries_of_origin';

    protected $fillable = [
        'name',
        'code',
        'flag_image_path',
    ];

    protected $appends = [
        'flag_image_url',
    ];

    public function products()
    {
        return $this->hasMany(CosmeticProduct::class, 'country_of_origin_id');
    }

    public function getFlagImageUrlAttribute(): ?string
    {
        if (!$this->flag_image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->flag_image_path);
    }
}


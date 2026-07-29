<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo_path',
        'status',
    ];

    protected $appends = [
        'logo_url',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) return null;
        return Storage::disk('public')->url($this->logo_path);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'brand_category');
    }
}

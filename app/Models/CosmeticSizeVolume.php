<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CosmeticSizeVolume extends Model
{
    use HasFactory;

    protected $table = 'cosmetic_size_volumes';

    protected $fillable = [
        'size',
        'unit',
    ];

    protected $casts = [
        'size' => 'decimal:2',
    ];

    public function variants()
    {
        return $this->hasMany(CosmeticProductVariant::class, 'cosmetic_size_volume_id');
    }

    public function getDisplayAttribute(): string
    {
        $size = rtrim(rtrim((string) ($this->size ?? ''), '0'), '.');

        return trim($size . ($this->unit ? $this->unit : ''));
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ColorOption extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'image_path', 'color_code', 'status'];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        return route('colors.image', $this->id);
    }

    public function normalizedColorCode(): string
    {
        $value = strtoupper(trim((string) $this->color_code));

        if (preg_match('/^#([A-F0-9]{3}|[A-F0-9]{6})$/', $value)) {
            return $value;
        }

        return '#D4D4D8';
    }
}
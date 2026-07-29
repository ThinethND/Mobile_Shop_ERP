<?php

namespace App\Models;

use App\Support\KokoPay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class KokoPaySetting extends Model
{
    protected $fillable = [
        'percentage',
    ];

    protected $casts = [
        'percentage' => 'float',
    ];

    private static ?float $cachedPercentage = null;

    public static function currentPercentage(): float
    {
        if (self::$cachedPercentage !== null) {
            return self::$cachedPercentage;
        }

        if (! Schema::hasTable('koko_pay_settings')) {
            return self::$cachedPercentage = 0.0;
        }

        return self::$cachedPercentage = KokoPay::percentage(
            static::query()->whereKey(1)->value('percentage') ?? 0
        );
    }

    public static function updatePercentage(mixed $value): self
    {
        self::$cachedPercentage = null;

        return static::query()->updateOrCreate(
            ['id' => 1],
            ['percentage' => KokoPay::percentage($value)]
        );
    }
}

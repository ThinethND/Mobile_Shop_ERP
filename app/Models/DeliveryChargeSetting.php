<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class DeliveryChargeSetting extends Model
{
    protected $fillable = [
        'cash_on_delivery_fee',
        'bank_transfer_delivery_fee',
    ];

    protected $casts = [
        'cash_on_delivery_fee' => 'float',
        'bank_transfer_delivery_fee' => 'float',
    ];

    private static ?array $cachedFees = null;

    public static function currentFees(): array
    {
        if (self::$cachedFees !== null) {
            return self::$cachedFees;
        }

        $defaults = [
            'cash_on_delivery_fee' => 450.0,
            'bank_transfer_delivery_fee' => 450.0,
        ];

        if (! Schema::hasTable('delivery_charge_settings')) {
            return self::$cachedFees = $defaults;
        }

        /** @var self|null $setting */
        $setting = static::query()->find(1);

        if (! $setting) {
            return self::$cachedFees = $defaults;
        }

        return self::$cachedFees = [
            'cash_on_delivery_fee' => round((float) ($setting->cash_on_delivery_fee ?? $defaults['cash_on_delivery_fee']), 2),
            'bank_transfer_delivery_fee' => round((float) ($setting->bank_transfer_delivery_fee ?? $defaults['bank_transfer_delivery_fee']), 2),
        ];
    }

    public static function updateFees(mixed $cashOnDeliveryFee, mixed $bankTransferDeliveryFee): self
    {
        self::$cachedFees = null;

        return static::query()->updateOrCreate(
            ['id' => 1],
            [
                'cash_on_delivery_fee' => round((float) ($cashOnDeliveryFee ?? 0), 2),
                'bank_transfer_delivery_fee' => round((float) ($bankTransferDeliveryFee ?? 0), 2),
            ]
        );
    }
}

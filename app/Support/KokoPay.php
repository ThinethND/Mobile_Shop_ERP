<?php

namespace App\Support;

class KokoPay
{
    public static function percentage(mixed $value): float
    {
        if (!is_numeric($value)) {
            return 0.0;
        }

        return max(0.0, (float) $value);
    }

    public static function installmentAmount(float|int $price, mixed $percentage): float
    {
        $price = max(0.0, (float) $price);

        if ($price <= 0) {
            return 0.0;
        }

        $payable = $price + (($price * self::percentage($percentage)) / 100);

        return floor(($payable / 3) * 100) / 100;
    }
}

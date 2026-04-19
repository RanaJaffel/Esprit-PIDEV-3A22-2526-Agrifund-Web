<?php

declare(strict_types=1);

namespace App\Payment;

final class PaymentProvider
{
    public const MANUAL = 'manual';

    private function __construct()
    {
    }

    public static function all(): array
    {
        return [
            self::MANUAL,
        ];
    }
}

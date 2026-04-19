<?php

declare(strict_types=1);

namespace App\Payment;

final class PaymentStatus
{
    public const INITIATED = 'initiated';
    public const PENDING = 'pending';
    public const SUCCEEDED = 'succeeded';
    public const FAILED = 'failed';
    public const CANCELED = 'canceled';

    private function __construct()
    {
    }

    public static function all(): array
    {
        return [
            self::INITIATED,
            self::PENDING,
            self::SUCCEEDED,
            self::FAILED,
            self::CANCELED,
        ];
    }
}

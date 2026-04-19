<?php

declare(strict_types=1);

namespace App\Payment;

final class AchatStatus
{
    public const INITIATED = 'initiated';
    public const PENDING_GATEWAY = 'pending_gateway';
    public const PAID = 'paid';
    public const FAILED = 'failed';
    public const EXPIRED = 'expired';
    public const REFUNDED = 'refunded';

    private function __construct()
    {
    }

    public static function all(): array
    {
        return [
            self::INITIATED,
            self::PENDING_GATEWAY,
            self::PAID,
            self::FAILED,
            self::EXPIRED,
            self::REFUNDED,
        ];
    }
}

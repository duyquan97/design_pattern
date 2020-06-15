<?php

namespace App\Booking\Model;

/**
 * Class BookingStatus
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingStatus
{
    public const CONFIRMED = 'Commit';
    public const CANCELLED = 'Cancel';
    public const CHOICES   = [
        'Confirmed' => self::CONFIRMED,
        'Canceled' => self::CANCELLED,
    ];
}

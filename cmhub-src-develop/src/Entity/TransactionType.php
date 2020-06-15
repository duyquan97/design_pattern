<?php

namespace App\Entity;

/**
 * Class TransactionType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
final class TransactionType
{
    public const AVAILABILITY = 'availability';
    public const PRICE = 'price';
    public const BOOKING = 'booking';
    public const CHOICES = [
        'Availability' => TransactionType::AVAILABILITY,
        'Booking' => TransactionType::BOOKING,
        'Price' => TransactionType::PRICE,
    ];
}

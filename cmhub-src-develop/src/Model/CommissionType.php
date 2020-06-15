<?php

namespace App\Model;

/**
 * Class CommissionType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class CommissionType
{
    public const PERCENTAGE = 'percentage';
    public const AMOUNT = 'amount';
    public const ALL = [
        self::AMOUNT,
        self::PERCENTAGE,
    ];

    public const CHOICES = [
        'Amount' => self::AMOUNT,
        'Percentage' => self::PERCENTAGE,
    ];
}

<?php

namespace App\Entity;

/**
 * Class TransactionStatus
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
final class TransactionStatus
{
    public const SCHEDULED = 'scheduled';

    public const FAILED = 'failed';

    public const SENT = 'sent';

    public const SUCCESS = 'success';

    public const ERROR = 'error';

    public const DEPRECATED = 'deprecated';

    public const ALL = [
        self::SCHEDULED,
        self::FAILED,
        self::SENT,
        self::SUCCESS,
        self::ERROR,
        self::DEPRECATED,
    ];

    public const CHOICES = [
        'Scheduled' => self::SCHEDULED,
        'Sent' => self::SENT,
        'Failed' => self::FAILED,
        'Success' => self::SUCCESS,
        'Error' => self::ERROR,
        'Deprecated' => self::DEPRECATED,
    ];
}

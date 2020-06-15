<?php

namespace App\Utils\Monolog;

/**
 * Class LogStatus
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
final class LogStatus
{
    public const SUCCESS = 'success';
    public const FAILED = 'failed';
    public const SKIPPED = 'skipped';
    public const PARTNER_NOT_VALID = 'partner_not_valid';
    public const PRODUCT_NOT_VALID = 'product_not_valid';
    public const VALIDATION_ERROR = 'validation_error';
}

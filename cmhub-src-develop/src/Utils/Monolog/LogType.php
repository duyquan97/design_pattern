<?php

namespace App\Utils\Monolog;

use App\Exception\CmHubException;
use App\Exception\IresaClientException;

/**
 * Class LogType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
final class LogType
{
    public const CMHUB_TYPE = 'cmhub_operation';
    public const CMHUB_EXCEPTION_TYPE = 'cmhub_exception';
    public const IRESA_EXCEPTION_TYPE = 'iresa_exception';
    public const PUSH_BOOKING_EXCEPTION_TYPE = 'booking_validation';
    public const EXCEPTION_TYPE = 'exception';
    public const CM_REQUEST_TYPE = 'cm_op';
    public const DB_TYPE = 'db';
    public const IRESA_TYPE = 'iresa';
    public const PERFORMANCE_TYPE = 'performance';
    public const KERNEL_REQUEST_TYPE = 'kernel_request';
    public const KERNEL_RESPONSE_TYPE = 'kernel_response';
    public const EAI = 'eai';
    public const PUSH_BOOKING = 'push_booking';
    public const TRANSACTION = 'transaction';
    public const MESSENGER = 'messenger';
    public const JARVIS = 'jb';

    /**
     * @param \Throwable $exception
     *
     * @return string
     */
    public static function getExceptionType(\Throwable $exception): string
    {
        if ($exception instanceof CmHubException) {
            return self::CMHUB_EXCEPTION_TYPE;
        }

        if ($exception instanceof IresaClientException) {
            return self::IRESA_EXCEPTION_TYPE;
        }

        return self::EXCEPTION_TYPE;
    }

    /**
     * @param \Throwable $exception
     *
     * @return string
     */
    public static function getExtendedExceptionType(\Throwable $exception): string
    {
        if (method_exists($exception, "getExceptionType")) {
            return $exception->getExceptionType();
        }

        return 'unknown';
    }
}

<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProductNotFoundException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class RatePlanNotFoundException extends \Exception
{
    const MESSAGE = 'Invalid rate id `%s`';
    const TYPE = 'rate_plan_not_found';

    /**
     * ProductNotFoundException constructor.
     *
     * @param string $ratePlanCode
     * @param int    $statusCode
     */
    public function __construct(string $ratePlanCode, int $statusCode = Response::HTTP_LENGTH_REQUIRED)
    {
        parent::__construct(sprintf(static::MESSAGE, $ratePlanCode), $statusCode);
    }

    /**
     *
     * @return string
     */
    public function getExceptionType(): string
    {
        return static::TYPE;
    }
}

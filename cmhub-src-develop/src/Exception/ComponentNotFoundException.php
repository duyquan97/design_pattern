<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class ComponentNotFoundException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ComponentNotFoundException extends CmHubException
{
    const MESSAGE = 'The product component code `%s` is not registered in SBX Channel Manager.';
    const TYPE = 'product_not_found';

    /**
     * ComponentNotFoundException constructor.
     *
     * @param string $roomTypeCode
     * @param int    $statusCode
     */
    public function __construct(string $roomTypeCode, int $statusCode = Response::HTTP_BAD_REQUEST)
    {
        parent::__construct(sprintf(static::MESSAGE, $roomTypeCode), $statusCode);
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

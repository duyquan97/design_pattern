<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class PartnerNotFoundException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PartnerNotFoundException extends CmHubException
{
    const MESSAGE = 'The partner with code `%s`  has not been found';
    const TYPE = 'partner_not_found';

    /**
     * PartnerNotFoundException constructor.
     *
     * @param string $code
     * @param int    $statusCode
     */
    public function __construct(string $code = null, int $statusCode = Response::HTTP_BAD_REQUEST)
    {
        parent::__construct(sprintf(static::MESSAGE, $code), $statusCode);
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

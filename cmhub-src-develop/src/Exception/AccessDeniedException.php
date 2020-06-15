<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class AccessDeniedException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AccessDeniedException extends CmHubException
{
    const MESSAGE = 'Access Denied';
    const TYPE = 'access_denied';

    /**
     * @var string
     */
    private $hotelCode;

    /**
     * AccessDeniedException constructor.
     *
     * @param int $statusCode
     * @param string $hotelCode
     */
    public function __construct(int $statusCode = Response::HTTP_FORBIDDEN, string $hotelCode = null)
    {
        parent::__construct(static::MESSAGE, $statusCode);
        $this->hotelCode = $hotelCode;
    }

    /**
     *
     * @return string
     */
    public function getExceptionType(): string
    {
        return static::TYPE;
    }

    /**
     *
     * @return string|null
     */
    public function getHotelCode(): ?string
    {
        return $this->hotelCode;
    }
}

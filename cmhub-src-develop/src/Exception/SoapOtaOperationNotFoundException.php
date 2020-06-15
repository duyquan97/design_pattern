<?php

namespace App\Exception;

/**
 * Class SoapOtaOperationNotFoundException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class SoapOtaOperationNotFoundException extends CmHubException
{
    public const MESSAGE = 'The soap method requested has not been found';
    public const TYPE = 'op_not_found';

    /**
     * RoomCodeNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(static::MESSAGE, 400);
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

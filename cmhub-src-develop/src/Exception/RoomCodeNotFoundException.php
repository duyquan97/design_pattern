<?php

namespace App\Exception;

/**
 * Class RoomCodeNotFoundException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class RoomCodeNotFoundException extends CmHubException
{
    const MESSAGE = 'The RoomTypeCode provided has not been found in Booking System';
    const TYPE = 'room_code_not_found';

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

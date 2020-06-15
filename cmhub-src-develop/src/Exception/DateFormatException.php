<?php

namespace App\Exception;

/**
 * Class DateFormatException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class DateFormatException extends CmHubException
{
    const MESSAGE = 'Wrong date format. Expected format is `%s`';
    const TYPE = 'date_format';

    /**
     * DateFormatException constructor.
     *
     * @param string $expectedFormat
     */
    public function __construct(string $expectedFormat)
    {
        parent::__construct(sprintf(static::MESSAGE, $expectedFormat), 400);
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

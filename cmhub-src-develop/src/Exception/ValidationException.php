<?php

namespace App\Exception;

/**
 * Class ValidationException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ValidationException extends CmHubException
{
    const TYPE = 'validation';

    /**
     * ValidationException constructor.
     *
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message, 400);
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

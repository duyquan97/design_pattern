<?php

namespace App\Exception;

/**
 * Class ValidationException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class EmptyRequestException extends CmHubException
{
    const TYPE = 'validation';
    public const MESSAGE = 'Transaction request is empty and can\'t be sent';

    /**
     * ValidationException constructor.
     */
    public function __construct()
    {
        parent::__construct(self::MESSAGE, 400);
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

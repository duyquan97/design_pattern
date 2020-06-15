<?php

namespace App\Exception;

/**
 * Class BB8OperationNotFoundException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BB8OperationNotFoundException extends CmHubException
{
    const MESSAGE = 'The BB8 action requested has not been found';

    /**
     * BB8OperationNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(static::MESSAGE, 400);
    }
}

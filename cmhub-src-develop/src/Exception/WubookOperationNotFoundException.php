<?php

namespace App\Exception;

/**
 * Class WubookOperationNotFoundException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class WubookOperationNotFoundException extends CmHubException
{
    const MESSAGE = 'The wubook action requested has not been found';

    /**
     * WubookOperationNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(static::MESSAGE, 400);
    }
}

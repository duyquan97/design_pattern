<?php

namespace App\Exception;

/**
 * Class SynchronizerNotFoundException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class SynchronizerNotFoundException extends CmHubException
{
    const MESSAGE = 'The synchronizer requested has not been found';

    /**
     * SerializerNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(static::MESSAGE, 404);
    }
}

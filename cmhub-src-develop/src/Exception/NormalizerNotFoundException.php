<?php

namespace App\Exception;

/**
 * Class SerializerNotFoundException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class NormalizerNotFoundException extends CmHubException
{
    const MESSAGE = 'The serializer requested has not been found';

    /**
     * SerializerNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct(static::MESSAGE, 404);
    }
}

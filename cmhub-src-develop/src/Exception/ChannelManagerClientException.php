<?php

namespace App\Exception;

/**
 * Class ChannelManagerClientException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ChannelManagerClientException extends CmHubException
{
    const TYPE = 'cm_client';

    /**
     *
     * @return string
     */
    public function getExceptionType(): string
    {
        return static::TYPE;
    }
}

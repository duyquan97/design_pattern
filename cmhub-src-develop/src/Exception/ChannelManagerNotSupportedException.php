<?php

namespace App\Exception;

/**
 * Class ChannelManagerNotSupportedException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ChannelManagerNotSupportedException extends CmHubException
{
    const TYPE = 'cm_not_supported';

    /**
     *
     * @return string
     */
    public function getExceptionType(): string
    {
        return static::TYPE;
    }
}

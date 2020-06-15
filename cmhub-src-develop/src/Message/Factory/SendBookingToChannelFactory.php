<?php

namespace App\Message\Factory;

use App\Message\SendBookingToChannel;

/**
 * Class SendBookingToChannelFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class SendBookingToChannelFactory
{
    /**
     *
     * @param string $identifier
     *
     * @return SendBookingToChannel
     */
    public function create(string $identifier)
    {
        return new SendBookingToChannel($identifier);
    }
}

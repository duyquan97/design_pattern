<?php

namespace App\Message\Factory;

use App\Message\PartnerChannelManagerUpdated;

/**
 * Class PartnerChannelManagerUpdatedFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PartnerChannelManagerUpdatedFactory
{
    /**
     *
     * @param string $identifier
     *
     * @return PartnerChannelManagerUpdated
     */
    public function create(string $identifier)
    {
        return new PartnerChannelManagerUpdated($identifier);
    }
}

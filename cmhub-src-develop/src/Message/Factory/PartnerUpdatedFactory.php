<?php

namespace App\Message\Factory;

use App\Message\PartnerUpdated;

/**
 * Class PartnerUpdatedFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PartnerUpdatedFactory
{
    /**
     *
     * @param string $identifier
     *
     * @return PartnerUpdated
     */
    public function create(string $identifier)
    {
        return new PartnerUpdated($identifier);
    }
}

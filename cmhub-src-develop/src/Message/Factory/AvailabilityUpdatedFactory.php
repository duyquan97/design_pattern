<?php

namespace App\Message\Factory;

use App\Entity\TransactionChannel;
use App\Message\AvailabilityUpdated;

/**
 * Class AvailabilityUpdatedFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailabilityUpdatedFactory
{
    /**
     *
     * @param array  $availabilityIds
     * @param string $channel
     *
     * @return AvailabilityUpdated
     */
    public function create(array $availabilityIds, string $channel = TransactionChannel::EAI)
    {
        return new AvailabilityUpdated($availabilityIds, $channel);
    }
}

<?php

namespace App\Message\Factory;

use App\Message\RateUpdated;

/**
 * Class RateUpdatedFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class RateUpdatedFactory
{
    /**
     *
     * @param array $rateIds
     * @param string $channel
     *
     * @return RateUpdated
     */
    public function create(array $rateIds, string $channel)
    {
        return new RateUpdated($rateIds, $channel);
    }
}

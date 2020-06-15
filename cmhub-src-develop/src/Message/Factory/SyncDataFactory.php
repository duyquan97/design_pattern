<?php

namespace App\Message\Factory;

use App\Message\SyncData;
use DateTime;

/**
 * Class SyncDataFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class SyncDataFactory
{
    /**
     *
     * @param string        $identifier
     * @param string        $type
     * @param DateTime|null $start
     * @param DateTime|null $end
     *
     * @return SyncData
     */
    public function create(string $identifier, string $type, DateTime $start = null, DateTime $end = null): SyncData
    {
        if (null === $start) {
            $start = date_create();
        }

        if (null === $end) {
            $end = date_create('+3 year');
        }

        return new SyncData($identifier, $start, $end, $type);
    }
}

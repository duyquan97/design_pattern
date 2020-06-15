<?php

namespace App\Message\Factory;

use App\Message\MasterProductUpdated;

/**
 * Class MasterProductUpdatedFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class MasterProductUpdatedFactory
{
    /**
     *
     * @param string $identifier
     *
     * @return MasterProductUpdated
     */
    public function create(string $identifier)
    {
        return new MasterProductUpdated($identifier);
    }
}

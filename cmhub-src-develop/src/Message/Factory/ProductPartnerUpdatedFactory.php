<?php

namespace App\Message\Factory;

use App\Message\ProductPartnerUpdated;

/**
 * Class ProductPartnerUpdatedFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductPartnerUpdatedFactory
{
    /**
     *
     * @param string $identifier
     *
     * @return ProductPartnerUpdated
     */
    public function create(string $identifier)
    {
        return new ProductPartnerUpdated($identifier);
    }
}

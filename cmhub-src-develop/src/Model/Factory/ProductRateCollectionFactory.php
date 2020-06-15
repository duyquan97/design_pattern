<?php

namespace App\Model\Factory;

use App\Model\PartnerInterface;
use App\Model\ProductRateCollection;

/**
 * Class ProductRateCollectionFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductRateCollectionFactory
{
    /**
     *
     * @param PartnerInterface $partner
     *
     * @return ProductRateCollection
     */
    public function create(PartnerInterface $partner): ProductRateCollection
    {
        return new ProductRateCollection($partner);
    }
}

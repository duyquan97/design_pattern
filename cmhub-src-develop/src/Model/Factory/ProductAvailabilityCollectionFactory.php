<?php

namespace App\Model\Factory;

use App\Model\PartnerInterface;
use App\Model\ProductAvailabilityCollection;

/**
 * Class ProductAvailabilityCollectionFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductAvailabilityCollectionFactory
{
    /**
     *
     * @param PartnerInterface $partner
     *
     * @return ProductAvailabilityCollection
     */
    public function create(PartnerInterface $partner)
    {
        return new ProductAvailabilityCollection($partner);
    }
}

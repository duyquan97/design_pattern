<?php

namespace App\Model\Factory;

use App\Model\ProductAvailability;
use App\Model\ProductInterface;

/**
 * Class ProductAvailabilityFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductAvailabilityFactory
{
    /**
     *
     * @param ProductInterface $product
     *
     * @return ProductAvailability
     */
    public function create(ProductInterface $product)
    {
        return new ProductAvailability($product);
    }
}

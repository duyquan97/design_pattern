<?php

namespace App\Model\Factory;

use App\Model\ProductInterface;
use App\Model\ProductRate;

/**
 * Class ProductRateFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductRateFactory
{
    /**
     *
     * @param ProductInterface $product
     *
     * @return ProductRate
     */
    public function create(ProductInterface $product)
    {
        return new ProductRate($product);
    }
}

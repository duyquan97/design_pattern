<?php

namespace App\Model\Factory;

use App\Model\BookingProduct;
use App\Model\ProductInterface;

/**
 * Class BookingProductFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingProductFactory
{
    /**
     *
     * @param ProductInterface $product
     *
     * @return BookingProduct
     */
    public function create(ProductInterface $product)
    {
        return new BookingProduct($product);
    }
}

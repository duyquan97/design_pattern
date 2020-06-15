<?php

namespace App\Model\Factory;

use App\Model\PartnerInterface;
use App\Model\ProductCollection;

/**
 * Class ProductCollectionFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductCollectionFactory
{
    /**
     *
     * @param PartnerInterface $partner
     * @param array $products
     *
     * @return ProductCollection
     */
    public function create(PartnerInterface $partner, array $products = array())
    {
        return new ProductCollection($partner, $products);
    }
}

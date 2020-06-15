<?php

namespace App\Entity\Factory;

use App\Entity\Product;

/**
 * Class ProductFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductFactory implements EntityFactoryInterface
{

    /**
     *
     * @return Product
     */
    public function create()
    {
        return new Product();
    }
}

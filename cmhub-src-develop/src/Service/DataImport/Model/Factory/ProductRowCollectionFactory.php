<?php

namespace App\Service\DataImport\Model\Factory;

use App\Entity\Factory\EntityFactoryInterface;
use App\Service\DataImport\Model\ProductRowCollection;

/**
 * Class ProductRowCollectionFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 *
 */
class ProductRowCollectionFactory implements EntityFactoryInterface
{

    /**
     * @return ProductRowCollection
     *
     */
    public function create()
    {
        return new ProductRowCollection();
    }
}

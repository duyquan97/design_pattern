<?php

namespace App\Service\DataImport\Model\Factory;

use App\Entity\Factory\EntityFactoryInterface;
use App\Service\DataImport\Model\ProductRow;

/**
 * Class ProductRowFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 *
 */
class ProductRowFactory implements EntityFactoryInterface
{

    /**
     * @return ProductRow
     *
     */
    public function create()
    {
        return new ProductRow();
    }
}

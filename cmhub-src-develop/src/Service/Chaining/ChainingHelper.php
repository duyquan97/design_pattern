<?php

namespace App\Service\Chaining;

use App\Model\ProductAvailabilityCollectionInterface;
use App\Model\ProductRateCollectionInterface;

/**
 * Class ChainingHelper
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ChainingHelper
{
    /**
     *
     * @param ProductAvailabilityCollectionInterface $productAvailabilities
     *
     * @return ProductAvailabilityCollectionInterface
     */
    public function chainAvailabilities(ProductAvailabilityCollectionInterface $productAvailabilities): ProductAvailabilityCollectionInterface
    {
        foreach ($productAvailabilities->getProductAvailabilities() as $productAvailability) {
            if ($productAvailability->getProduct()->hasLinkedProducts()) {
                foreach ($productAvailability->getProduct()->getLinkedProducts() as $linkedProduct) {
                    $linkedProductAvailability = $productAvailability->cloneInstance();
                    $linkedProductAvailability->setProduct($linkedProduct);
                    $productAvailabilities->addProductAvailability($linkedProductAvailability);
                }
            }
        }

        return $productAvailabilities;
    }

    /**
     *
     * @param ProductRateCollectionInterface $productRateCollection
     *
     * @return ProductRateCollectionInterface
     */
    public function chainRates(ProductRateCollectionInterface $productRateCollection): ProductRateCollectionInterface
    {
        foreach ($productRateCollection->getProductRates() as $productRate) {
            if ($productRate->getProduct()->hasLinkedProducts()) {
                foreach ($productRate->getProduct()->getLinkedProducts() as $linkedProduct) {
                    $linkedProductRate = $productRate->cloneInstance();
                    $linkedProductRate->setProduct($linkedProduct);
                    $productRateCollection->addProductRate($linkedProductRate);
                }
            }
        }

        return $productRateCollection;
    }
}

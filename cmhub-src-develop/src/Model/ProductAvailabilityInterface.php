<?php

namespace App\Model;

/**
 * Interface ProductAvailabilityInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface ProductAvailabilityInterface
{
    /**
     *
     * @return ProductInterface
     */
    public function getProduct(): ProductInterface;

    /**
     *
     * @param ProductInterface $product
     *
     * @return ProductAvailabilityInterface
     */
    public function setProduct(ProductInterface $product);

    /**
     *
     * @return AvailabilityInterface[]
     */
    public function getAvailabilities(): array;

    /**
     *
     * @param AvailabilityInterface $availability
     *
     * @return $this
     */
    public function addAvailability(AvailabilityInterface $availability);

    /**
     *
     * @return ProductAvailabilityInterface
     */
    public function cloneInstance();
}

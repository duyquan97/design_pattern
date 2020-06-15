<?php

namespace App\Model;

/**
 * Interface ProductRateInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface ProductRateInterface
{
    /**
     *
     * @return RateInterface[]
     */
    public function getRates();

    /**
     *
     * @return ProductInterface
     */
    public function getProduct(): ProductInterface;

    /**
     *
     * @param RateInterface $rate
     *
     * @return mixed
     */
    public function addRate(RateInterface $rate);

    /**
     *
     * @return ProductRateInterface
     */
    public function cloneInstance();

    /**
     *
     * @param ProductInterface $product
     *
     * @return $this
     */
    public function setProduct(ProductInterface $product);
}

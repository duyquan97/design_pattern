<?php

namespace App\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Interface BookingProductInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface BookingProductInterface
{
    /**
     *
     * @return ProductInterface|null
     */
    public function getProduct();

    /**
     *
     * @param ProductInterface $product
     *
     * @return BookingProductInterface
     */
    public function setProduct(ProductInterface $product);

    /**
     *
     * @return float
     */
    public function getAmount();

    /**
     *
     * @param float $amount
     *
     * @return BookingProductInterface
     */
    public function setAmount(float $amount);

    /**
     *
     * @return BookingInterface|null
     */
    public function getBooking();

    /**
     *
     * @return RateInterface[]
     */
    public function getRates();

    /**
     *
     * @param \DateTime $date
     *
     * @return bool
     */
    public function hasRate(\DateTime $date);

    /**
     *
     * @param RateInterface $rate
     *
     * @return $this
     */
    public function addRate(RateInterface $rate);

    /**
     *
     * @return GuestInterface[]|ArrayCollection
     */
    public function getGuests();

    /**
     *
     * @return int
     */
    public function getTotalGuests();
}

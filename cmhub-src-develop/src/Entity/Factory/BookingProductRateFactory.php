<?php

namespace App\Entity\Factory;

use App\Entity\BookingProductRate;
use App\Model\BookingProductInterface;

/**
 * Class BookingProductRateFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingProductRateFactory
{
    /**
     *
     * @param BookingProductInterface $bookingProduct
     * @param \DateTime               $date
     * @param float                   $amount
     * @param string                  $currency
     *
     * @return BookingProductRate
     */
    public function create(BookingProductInterface $bookingProduct, \DateTime $date, float $amount, string $currency)
    {
        return (new BookingProductRate())
            ->setBookingProduct($bookingProduct)
            ->setDate(clone $date)
            ->setAmount($amount)
            ->setCurrency($currency);
    }
}

<?php

namespace App\Booking\Processor;

use App\Model\BookingInterface;
use App\Model\BookingProduct;
use App\Booking\BookingProcessorInterface;

/**
 * Class ProductBookingAmountProcessor
 *
 * This processor sets the total amount of the product booking based on the daily rates summing all daily rates.
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingProductVoucherProcessor implements BookingProcessorInterface
{
    /**
     *
     * @param BookingInterface $booking
     *
     * @return BookingInterface
     */
    public function process(BookingInterface $booking): BookingInterface
    {
        $booking->setComments(sprintf('Voucher Number: %s. %s', $booking->getVoucherNumber() ?: 'X', $booking->getComments()));

        return $booking;
    }
}
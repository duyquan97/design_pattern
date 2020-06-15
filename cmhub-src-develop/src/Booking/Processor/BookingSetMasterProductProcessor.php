<?php

namespace App\Booking\Processor;

use App\Model\BookingInterface;
use App\Booking\BookingProcessorInterface;

/**
 * Class BookingSetMasterProductProcessor
 *
 * This processor sets the master product in case the booking was made in a child room
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingSetMasterProductProcessor implements BookingProcessorInterface
{
    /**
     *
     * @param BookingInterface $booking
     *
     * @return BookingInterface
     */
    public function process(BookingInterface $booking): BookingInterface
    {
        foreach ($booking->getBookingProducts() as $bookingProduct) {
            if (!$bookingProduct->getProduct()->isMaster()) {
                $bookingProduct->setProduct($bookingProduct->getProduct()->getMasterProduct());
            }
        }

        return $booking;
    }
}

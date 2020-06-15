<?php

namespace App\Booking\Processor;

use App\Model\BookingInterface;
use App\Booking\BookingProcessorInterface;

/**
 * Class BookingAmountProcessor
 *
 * This processor sets the total amount of the booking based on the daily rates summing all daily rates.
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingAmountProcessor implements BookingProcessorInterface
{
    /**
     *
     * @param BookingInterface $booking
     *
     * @return BookingInterface
     */
    public function process(BookingInterface $booking): BookingInterface
    {
        $booking->setTotalAmount(0);
        foreach ($booking->getRates() as $rate) {
            $booking->addTotalAmount($rate->getAmount());
        }

        return $booking;
    }
}

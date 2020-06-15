<?php

namespace App\Booking\Processor;

use App\Booking\BookingProcessorInterface;
use App\Model\BookingInterface;

/**
 * Class BookingGuestProcessor
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingGuestProcessor implements BookingProcessorInterface
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
            $guests = [];
            foreach ($bookingProduct->getGuests() as $guest) {
                if (!empty($guest->getName())) {
                    $guests[] = $guest;
                }
            }

            $bookingProduct->setGuests($guests);
        }

        return $booking;
    }
}

<?php

namespace App\Booking\Processor;

use App\Model\BookingInterface;
use App\Booking\BookingProcessorInterface;

/**
 * Class BookingExperiencePriceProcessor
 *
 * This processor sets the booking daily rates based on the experience price. It divides the experience price by the number of nights
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingExperiencePriceProcessor implements BookingProcessorInterface
{
    /**
     *
     * @param BookingInterface $booking
     *
     * @return BookingInterface
     */
    public function process(BookingInterface $booking): BookingInterface
    {
        $expDays = 0;
        foreach ($booking->getRates() as $rate) {
            if ($rate->getAmount() <= 0) {
                $expDays++;
            }
        }

        if (0 === $expDays) {
            return $booking;
        }

        $dailyPrice = $booking->getExperience() ? $booking->getExperience()->getPrice() / $expDays : 0;

        foreach ($booking->getRates() as $rate) {
            if ($rate->getAmount() <= 0) {
                $rate->setAmount($dailyPrice);
            }
        }

        return $booking;
    }
}

<?php

namespace App\Booking;

use App\Exception\BookingAlreadyProcessedException;
use App\Model\BookingInterface;

/**
 * Interface BookingProcessorInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface BookingProcessorInterface
{
    /**
     *
     * @param BookingInterface $booking
     *
     * @return BookingInterface
     *
     * @throws BookingAlreadyProcessedException
     */
    public function process(BookingInterface $booking): BookingInterface;
}

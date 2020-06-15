<?php

namespace App\Exception;

use App\Model\BookingInterface;

/**
 * Class BookingAlreadyProcessedException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingAlreadyProcessedException extends \Exception
{
    /**
     * BookingAlreadyProcessedException constructor.
     *
     * @param BookingInterface $booking
     */
    public function __construct(BookingInterface $booking)
    {
        parent::__construct(sprintf('Booking with id %s has been already processed', $booking->getReservationId()));
    }
}

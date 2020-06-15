<?php

namespace App\Message;

use App\Booking\Model\Booking;

/**
 * Class BookingReceived
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingReceived extends AbstractMessage
{
    /**
     * @var Booking
     */
    private $booking;

    /**
     * BookingCreated constructor.
     *
     * @param Booking $booking
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     *
     * @return Booking
     */
    public function getBooking(): Booking
    {
        return $this->booking;
    }
}

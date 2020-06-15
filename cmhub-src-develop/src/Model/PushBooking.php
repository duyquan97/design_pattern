<?php

namespace App\Model;

use App\Entity\Booking as BookingEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PushBooking
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PushBooking
{
    /**
     *
     * @var BookingEntity
     *
     * @Assert\Valid()
     */
    private $booking;

    /**
     *
     * @return BookingEntity
     */
    public function getBooking(): BookingEntity
    {
        return $this->booking;
    }

    /**
     *
     * @param BookingEntity $booking
     *
     * @return $this
     */
    public function setBooking(BookingEntity $booking): PushBooking
    {
        $this->booking = $booking;

        return $this;
    }
}

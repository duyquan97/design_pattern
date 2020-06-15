<?php

namespace App\Model\Factory;

use App\Model\Booking;

/**
 * Class BookingFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingFactory
{
    /**
     *
     * @return Booking
     */
    public function create()
    {
        return new Booking();
    }
}

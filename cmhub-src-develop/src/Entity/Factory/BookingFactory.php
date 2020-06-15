<?php

namespace App\Entity\Factory;

use App\Entity\Booking;

/**
 * Class BookingFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingFactory implements EntityFactoryInterface
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

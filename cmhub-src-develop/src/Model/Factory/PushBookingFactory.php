<?php

namespace App\Model\Factory;

use App\Model\PushBooking;

/**
 * Class PushBookingFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PushBookingFactory
{
    /**
     *
     * @return PushBooking
     */
    public function create()
    {
        return new PushBooking();
    }
}

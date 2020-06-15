<?php

namespace App\Message\Factory;

use App\Message\BookingCreated;

/**
 * Class BookingCreatedFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingCreatedFactory
{
    /**
     *
     * @param string $identifier
     *
     * @return BookingCreated
     */
    public function create(string $identifier)
    {
        return new BookingCreated($identifier);
    }
}

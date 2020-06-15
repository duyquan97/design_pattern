<?php

namespace App\Model;

/**
 * Interface BookingCollectionInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface BookingCollectionInterface
{
    /**
     *
     * @return array
     */
    public function getBookings(): array;
}

<?php

namespace App\Model\Factory;

use App\Model\BookingCollection;
use App\Model\PartnerInterface;

/**
 * Class BookingCollectionFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingCollectionFactory
{
    /**
     *
     * @return BookingCollection
     */
    public function create()
    {
        return new BookingCollection();
    }
}

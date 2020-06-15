<?php

namespace App\Repository\Listener;

use App\Entity\Booking;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 * Class BookingListener
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingListener
{
    /**
     * @param Booking $booking
     *
     * @return void
     */
    public function postPersist(Booking $booking): void
    {
        $booking->setChannelManager($booking->getPartner()->getChannelManager());
    }
}

<?php

namespace App\Booking\Processor;

use App\Model\BookingInterface;
use App\Booking\BookingProcessorInterface;

/**
 * Class BookingSetChannelProcessor
 *
 * This processor sets the channel manager in the Booking entity based on partner->channelManager
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingSetChannelProcessor implements BookingProcessorInterface
{
    /**
     *
     * @param BookingInterface $booking
     *
     * @return BookingInterface
     */
    public function process(BookingInterface $booking): BookingInterface
    {
        return $booking->setChannelManager($booking->getPartner()->getChannelManager());
    }
}

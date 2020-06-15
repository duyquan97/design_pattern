<?php

namespace spec\App\Repository\Listener;

use App\Entity\Booking;
use App\Entity\ChannelManager;
use App\Repository\Listener\BookingListener;
use App\Entity\Partner;
use PhpSpec\ObjectBehavior;


class BookingListenerSpec extends ObjectBehavior
{

    function it_is_initializable()
    {
        $this->shouldHaveType(BookingListener::class);
    }

    function it_update_bookig_chennel_manager_with_partner_chennel_manager(Booking $booking, Partner $partner,ChannelManager $channelManager)
    {
        $booking->getPartner()->willReturn($partner)->shouldBeCalledOnce();
        $partner->getChannelManager()->willReturn($channelManager)->shouldBeCalledOnce();
        $booking->setChannelManager($channelManager)->shouldBeCalledOnce();
        $this->postPersist($booking);
    }

}

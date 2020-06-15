<?php

namespace spec\App\Booking\Processor;

use App\Entity\ChannelManager;
use App\Model\BookingInterface;
use App\Model\PartnerInterface;
use App\Booking\Processor\BookingSetChannelProcessor;
use PhpSpec\ObjectBehavior;

class BookingSetChannelProcessorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingSetChannelProcessor::class);
    }

    function it_sets_channel_manager_to_booking_from_partner(
        BookingInterface $booking,
        ChannelManager $channelManager,
        PartnerInterface $partner
    )
    {
        $booking->getPartner()->willReturn($partner);
        $partner->getChannelManager()->willReturn($channelManager);
        $booking->setChannelManager($channelManager)->shouldBeCalled()->willReturn($booking);

        $this->process($booking)->shouldBe($booking);
    }
}

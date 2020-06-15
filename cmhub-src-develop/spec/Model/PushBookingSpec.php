<?php

namespace spec\App\Model;

use App\Model\PushBooking;
use App\Entity\Booking as BookingEntity;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PushBookingSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PushBooking::class);
    }

    function it_sets_gets_booking(BookingEntity $booking)
    {
        $this->setBooking($booking);
        $this->getBooking()->shouldBe($booking);
    }
}

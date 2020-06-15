<?php

namespace spec\App\Booking\Processor;

use App\Booking\Processor\BookingGuestProcessor;
use App\Entity\BookingProduct;
use App\Entity\Guest;
use App\Model\BookingInterface;
use PhpSpec\ObjectBehavior;

class BookingGuestProcessorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingGuestProcessor::class);
    }

    function it_removes_guests_with_empty_name(BookingInterface $booking, BookingProduct $bookingProduct, Guest $guest, Guest $guest1)
    {
        $booking->getBookingProducts()->willReturn([$bookingProduct]);
        $bookingProduct->getGuests()->willReturn([
            $guest,
            $guest1
        ]);

        $guest->getName()->willReturn('asdsad');
        $guest1->getName()->willReturn('');

        $bookingProduct->setGuests([$guest])->shouldBeCalled();

        $this->process($booking)->shouldBe($booking);
    }
}

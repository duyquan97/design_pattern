<?php

namespace spec\App\Booking;

use App\Model\BookingInterface;
use App\Booking\BookingProcessorInterface;
use App\Booking\BookingProcessorManager;
use PhpSpec\ObjectBehavior;

class BookingProcessorManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingProcessorManager::class);
    }

    function let(BookingProcessorInterface $processor, BookingProcessorInterface $processor1)
    {
        $this->beConstructedWith([
            $processor,
            $processor1
        ]);
    }

    function it_process_booking(BookingInterface $booking, BookingProcessorInterface $processor, BookingProcessorInterface $processor1)
    {
        $processor->process($booking)->shouldBeCalled()->willReturn($booking);
        $processor1->process($booking)->shouldBeCalled()->willReturn($booking);
        $this->process($booking)->shouldBe($booking);
    }
}

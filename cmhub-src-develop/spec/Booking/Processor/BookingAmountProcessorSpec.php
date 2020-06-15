<?php

namespace spec\App\Booking\Processor;

use App\Model\BookingInterface;
use App\Model\RateInterface;
use App\Booking\Processor\BookingAmountProcessor;
use PhpSpec\ObjectBehavior;

class BookingAmountProcessorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingAmountProcessor::class);
    }

    function it_sets_total_amount(BookingInterface $booking, RateInterface $rate, RateInterface $rate1)
    {
        $booking->setTotalAmount(0)->shouldBeCalled()->willReturn($booking);
        $booking->getRates()->willReturn([
            $rate,
            $rate1
        ]);
        $rate->getAmount()->willReturn(1.50);
        $rate1->getAmount()->willReturn(2.50);
        $booking->addTotalAmount(1.50)->shouldBeCalledOnce();
        $booking->addTotalAmount(2.50)->shouldBeCalledOnce();

        $this->process($booking)->shouldBe($booking);
    }
}

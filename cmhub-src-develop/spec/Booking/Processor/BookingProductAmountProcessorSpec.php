<?php

namespace spec\App\Booking\Processor;

use App\Model\BookingInterface;
use App\Model\BookingProduct;
use App\Model\RateInterface;
use App\Booking\Processor\BookingProductAmountProcessor;
use PhpSpec\ObjectBehavior;

class BookingProductAmountProcessorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingProductAmountProcessor::class);
    }

    function it_sets_total_amount(BookingInterface $booking, BookingProduct $bookingProduct, BookingProduct $bookingProduct1, RateInterface $rate, RateInterface $rate1, RateInterface $rate2, RateInterface $rate3)
    {
        $booking->getBookingProducts()->willReturn([
            $bookingProduct,
            $bookingProduct1
        ]);
        $bookingProduct->getRates()->willReturn([
            $rate,
            $rate1
        ]);
        $bookingProduct1->getRates()->willReturn([
            $rate2,
            $rate3
        ]);
        $rate->getAmount()->willReturn(1.50);
        $rate1->getAmount()->willReturn(2.50);
        $rate2->getAmount()->willReturn(1.60);
        $rate3->getAmount()->willReturn(2.80);
        $bookingProduct->setAmount(4.0)->shouldBeCalledOnce();
        $bookingProduct1->setAmount(4.40)->shouldBeCalledOnce();

        $this->process($booking)->shouldBe($booking);
    }
}

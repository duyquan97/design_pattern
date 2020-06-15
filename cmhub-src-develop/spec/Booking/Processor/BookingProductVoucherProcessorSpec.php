<?php

namespace spec\App\Booking\Processor;

use App\Entity\Booking;
use App\Booking\Processor\BookingComponentsProcessor;
use App\Booking\JarvisClient;
use App\Booking\Processor\BookingProductVoucherProcessor;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class BookingProductVoucherProcessorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingProductVoucherProcessor::class);
    }

    function it_sets_voucher_into_comment(
        Booking $booking
    ) {

        $booking->getComments()->willReturn('previous');
        $booking->getVoucherNumber()->willReturn('abc123');
        $booking->setComments('Voucher Number: abc123. previous')->willReturn($booking)->shouldBeCalled();
        $this->process($booking)->shouldBe($booking);
    }

    function it_no_voucher_number(
        Booking $booking
    ) {

        $booking->getComments()->willReturn('previous');
        $booking->getVoucherNumber()->willReturn(false);
        $booking->setComments('Voucher Number: X. previous')->willReturn($booking)->shouldBeCalled();
        $this->process($booking)->shouldBe($booking);
    }
}

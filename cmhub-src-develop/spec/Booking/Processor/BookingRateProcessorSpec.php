<?php

namespace spec\App\Booking\Processor;

use App\Entity\BookingProductRate;
use App\Entity\Factory\BookingProductRateFactory;
use App\Model\BookingInterface;
use App\Model\BookingProductInterface;
use App\Model\PartnerInterface;
use App\Booking\Processor\BookingRateProcessor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BookingRateProcessorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingRateProcessor::class);
    }

    function let(BookingProductRateFactory $rateFactory)
    {
        $this->beConstructedWith($rateFactory);
    }

    function it_creates_missing_rates(
        BookingInterface $booking,
        BookingProductInterface $bookingProduct,
        PartnerInterface $partner,
        BookingProductRateFactory $rateFactory,
        BookingProductRate $bookingProductRate,
        BookingProductRate $bookingProductRate1
    )
    {
        $booking->getPartner()->willReturn($partner);
        $partner->getCurrency()->willReturn('pepito');
        $booking->getBookingProducts()->willReturn([$bookingProduct]);
        $booking->getStartDate()->willReturn($startDate = new \DateTime('2020-01-01 00:00:00'));
        $booking->getEndDate()->willReturn($endDate = new \DateTime('2020-01-03 00:00:00'));
        $bookingProduct
            ->hasRate(
                Argument::that(function (\DateTime $date) {
                    return $date->format('Y-m-d') === '2020-01-01';
                })
            )
            ->willReturn(false);

        $rateFactory->create($bookingProduct, $startDate, 0, 'pepito')->shouldBeCalled()->willReturn($bookingProductRate);
        $bookingProduct->addRate($bookingProductRate)->shouldBeCalled();

        $bookingProduct
            ->hasRate(
                Argument::that(function (\DateTime $date) {
                    return $date->format('Y-m-d') === '2020-01-02';
                })
            )
            ->willReturn(false);

        $rateFactory
            ->create(
                $bookingProduct,
                Argument::that(function (\DateTime $date) {
                    return $date->format('Y-m-d') === '2020-01-02';
                }),
                0,
                'pepito'
            )
            ->shouldBeCalled()
            ->willReturn($bookingProductRate1);

        $bookingProduct->addRate($bookingProductRate1)->shouldBeCalled();

        $this->process($booking)->shouldBe($booking);
    }
}

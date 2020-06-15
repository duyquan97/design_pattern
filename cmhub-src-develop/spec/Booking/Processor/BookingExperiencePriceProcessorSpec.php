<?php

namespace spec\App\Booking\Processor;

use App\Model\BookingInterface;
use App\Model\ExperienceInterface;
use App\Model\RateInterface;
use App\Booking\Processor\BookingExperiencePriceProcessor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BookingExperiencePriceProcessorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingExperiencePriceProcessor::class);
    }

    function it_splits_experience_price_to_days_with_zero_amount(
        RateInterface $rate,
        RateInterface $rate1,
        RateInterface $rate2,
        ExperienceInterface $experience,
        BookingInterface $booking
    )
    {
        $booking->getRates()->willReturn([
            $rate,
            $rate1,
            $rate2
        ]);
        $rate->getAmount()->willReturn(0);
        $rate1->getAmount()->willReturn(0);
        $rate2->getAmount()->willReturn(10.50);
        $booking->getExperience()->willReturn($experience);
        $experience->getPrice()->willReturn(100);
        $rate->setAmount(50)->shouldBeCalledOnce();
        $rate1->setAmount(50)->shouldBeCalledOnce();

        $this->process($booking)->shouldBe($booking);
    }

    function it_does_not_split_if_rates_bigger_than_zero(
        RateInterface $rate,
        RateInterface $rate1,
        RateInterface $rate2,
        BookingInterface $booking
    )
    {
        $booking->getRates()->willReturn([
            $rate,
            $rate1,
            $rate2
        ]);
        $rate->getAmount()->willReturn(110);
        $rate1->getAmount()->willReturn(112);
        $rate2->getAmount()->willReturn(10.50);
        $rate->setAmount(Argument::any())->shouldNotBeCalled();
        $rate1->setAmount(Argument::any())->shouldNotBeCalled();
        $rate2->setAmount(Argument::any())->shouldNotBeCalled();

        $this->process($booking)->shouldBe($booking);
    }
}

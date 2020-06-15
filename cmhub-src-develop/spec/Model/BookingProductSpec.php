<?php

namespace spec\App\Model;

use App\Model\BookingInterface;
use App\Model\BookingProduct;
use App\Model\GuestInterface;
use App\Model\ProductInterface;
use App\Model\RateInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BookingProductSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingProduct::class);
    }

    function let(ProductInterface $product)
    {
        $this->beConstructedWith($product);
    }

    function it_gets_and_sets_amount_attribute()
    {
        $this->setAmount(20.36);
        $this->getAmount()->shouldBe(20.36);
    }

    function it_gets_and_sets_currency_attribute()
    {
        $this->setCurrency('EUR');
        $this->getCurrency()->shouldBe('EUR');
    }

    function it_gets_sets_and_adds_guests_attribute(GuestInterface $guest, GuestInterface $guest1, GuestInterface $guest2)
    {
        $this->setGuests($guests = [$guest, $guest1]);
        $this->getGuests()->shouldBe($guests);
        $this->addGuest($guests[] = $guest2);
        $this->getGuests()->shouldBe($guests);
    }

    function it_gets_sets_and_adds_rates_attribute(RateInterface $rate, RateInterface $rate1, RateInterface $rate2)
    {
        $this->setRates($rates = [$rate, $rate1]);
        $this->getRates()->shouldBe($rates);
        $this->addRate($rates[] = $rate2);
        $this->getRates()->shouldBe($rates);
    }

    function it_gets_and_sets_booking_attribute(BookingInterface $booking)
    {
        $this->setBooking($booking);
        $this->getBooking()->shouldBe($booking);
    }

    function it_gets_and_sets_product_attribute(ProductInterface $product)
    {
        $this->setProduct($product);
        $this->getProduct()->shouldBe($product);
    }
}

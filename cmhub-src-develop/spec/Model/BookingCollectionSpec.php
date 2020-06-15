<?php

namespace spec\App\Model;

use App\Model\BookingCollection;
use App\Model\BookingInterface;
use App\Model\PartnerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BookingCollectionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingCollection::class);
    }

    function it_gets_sets_and_add_bookings(BookingInterface $booking, BookingInterface $booking1, BookingInterface $booking2)
    {
        $this->setBookings($bookingCollection = [$booking, $booking1]);
        $this->getBookings()->shouldBe($bookingCollection);

        $this->addBooking($booking2);
        $bookingCollection[] = $booking2;
        $this->getBookings()->shouldBe($bookingCollection);

    }

    function it_returns_current_bookings(BookingInterface $booking, BookingInterface $booking1)
    {
        $this->setBookings([$booking, $booking1]);
        $this->current()->shouldBe($booking);
        $this->next();
        $this->current()->shouldBe($booking1);
    }

    function it_returns_index_attribute()
    {
        $this->next();
        $this->key()->shouldBe(1);
    }

    function it_returns_true_if_valid_or_false_if_not_valid(BookingInterface $booking, BookingInterface $booking1)
    {
        $this->valid()->shouldBe(false);
        $this->setBookings([$booking, $booking1]);
        $this->valid()->shouldBe(true);
    }

    function it_sets_index_as_zero()
    {
        $this->next();
        $this->next();
        $this->rewind();
        $this->key()->shouldBe(0);
    }
}

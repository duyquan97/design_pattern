<?php

namespace spec\App\Model;

use App\Model\Booking;
use App\Model\BookingProduct;
use App\Model\BookingProductInterface;
use App\Model\Guest;
use App\Model\GuestInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BookingSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Booking::class);
    }

    function it_gets_and_sets_status_attribute()
    {
        $this->setStatus('Bookable');
        $this->getStatus()->shouldBe('Bookable');
    }

    function it_gets_and_sets_reservationId_attribute()
    {
        $this->setReservationId('201538');
        $this->getReservationId()->shouldBe('201538');
    }

    function it_gets_and_sets_startDate_attribute(\DateTime $startDate)
    {
        $this->setStartDate($startDate);
        $this->getStartDate()->shouldBe($startDate);
    }

    function it_gets_and_sets_endDate_attribute(\DateTime $endDate)
    {
        $this->setEndDate($endDate);
        $this->getEndDate()->shouldBe($endDate);
    }

    function it_gets_and_sets_totalAmount_attribute()
    {
        $this->setTotalAmount(floatval(123.456));
        $this->getTotalAmount()->shouldBe(floatval(123.456));
    }

    function it_gets_and_sets_currency_attribute()
    {
        $this->setCurrency('EUR');
        $this->getCurrency()->shouldBe('EUR');
    }

    function it_gets_and_sets_createdAt_attribute(\DateTime $createdAt)
    {
        $this->setCreatedAt($createdAt);
        $this->getCreatedAt()->shouldBe($createdAt);
    }

    function it_gets_and_sets_updatedAt_attribute(\DateTime $updatedAt)
    {
        $this->setUpdatedAt($updatedAt);
        $this->getUpdatedAt()->shouldBe($updatedAt);
    }

    function it_gets_sets_and_adds_bookingProduct_attribute(BookingProductInterface $bookingProduct, BookingProductInterface $bookingProduct1, BookingProductInterface $bookingProduct2)
    {

        $this->setBookingProducts($products  = [$bookingProduct, $bookingProduct1]);
        $this->getBookingProducts()->shouldBe($products);

        $this->addBookingProduct($bookingProduct2);
        $products[] = $bookingProduct2;
        $this->getBookingProducts()->shouldBe($products);
    }

    function it_gets_and_sets_comments_attribute()
    {
        $this->setComments('jijaju');
        $this->getComments()->shouldBe('jijaju');
    }

    function it_gets_and_sets_requests_attribute()
    {
        $this->setRequests('random request');
        $this->getRequests()->shouldBe('random request');
    }

    function it_gets_firstBooking(BookingProductInterface $bookingProduct, BookingProductInterface $bookingProduct1)
    {
        $this->setBookingProducts([$bookingProduct, $bookingProduct1]);
        $this->firstBookingProduct()->shouldBe($bookingProduct);
    }

    function it_gets_guests(BookingProduct $bookingProduct, Guest $guest1, Guest $guest2)
    {
        $bookingProduct->getGuests()->willReturn($guests = [$guest1, $guest2]);
        $this->addBookingProduct($bookingProduct);
        $this->getGuests()->shouldBe($guests);
    }

    function it_gets_totalGuests(BookingProduct $bookingProduct, Guest $guest1, Guest $guest2)
    {
        $this->it_gets_guests($bookingProduct, $guest1, $guest2);
        $this->getTotalGuests()->shouldBe(2);
    }
}

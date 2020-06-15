<?php

namespace spec\App\MessageHandler;

use App\Booking\BookingManager;
use App\Booking\Model\Booking;
use App\Message\BookingReceived;
use App\MessageHandler\BookingReceivedHandler;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\MessageBusInterface;

class BookingReceivedHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingReceivedHandler::class);
    }

    function let(
        BookingManager $bookingManager
    )
    {

        $this->beConstructedWith($bookingManager);
    }

    function it_handles_process_new_booking_message(
        BookingReceived $message,
        BookingManager $bookingManager,
        Booking $booking1
    )
    {
        $message->getBooking()->willReturn($booking1);
        $bookingManager->create($booking1)->shouldBeCalled();
        $this->__invoke($message);
    }
}

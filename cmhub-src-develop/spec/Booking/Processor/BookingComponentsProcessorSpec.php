<?php

namespace spec\App\Booking\Processor;

use App\Booking\Processor\BookingComponentsProcessor;
use App\Entity\Booking;
use App\Service\Booking\JarvisClient;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;

class BookingComponentsProcessorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingComponentsProcessor::class);
    }

    function let(JarvisClient $jarvisHttpClient,CmhubLogger $logger)
    {
        $this->beConstructedWith($jarvisHttpClient, $logger);
    }

    function it_does_not_process_if_booking_has_components(
        Booking $booking
    ) {
        $booking->getComponents()->willReturn(['a', 'b']);
        $booking->getComments()->willReturn('prev');
        $booking->setComments('prev. a | b')->shouldBeCalled()->willReturn($booking);

        $this->process($booking)->shouldBe($booking);
    }

    function it_sets_comment(
        Booking $booking,
        JarvisClient $jarvisHttpClient,
        \stdClass $response
    )
    {
        $booking->getComponents()->willReturn([]);
        $booking->isConfirmed()->willReturn(true)->shouldBeCalled();
        $response->experience = new \stdClass();
        $obj = (object) array(
            'name' => 'one'
        );
        $obj2 = (object) array(
            'name' => 'two'
        );
        $response->experience->components = [
            $obj,
            $obj2
        ];
        $booking->getReservationId()->willReturn('123');
        $booking->getComments()->willReturn('previous');
        $jarvisHttpClient->getBookingDetails('123')->willReturn($response);

        $booking->setComponents(['one', 'two'])->shouldBeCalled()->willReturn($booking);
        $booking->setComments('previous | one | two')->willReturn($booking)->shouldBeCalled();
        $this->process($booking)->shouldBe($booking);
    }

    function it_not_confirmed(
        Booking $booking,
        JarvisClient $jarvisHttpClient,
        \stdClass $response
    )
    {
        $booking->getComponents()->willReturn([]);
        $booking->isConfirmed()->willReturn(false);
        $response->experience = new \stdClass();
        $response->experience->components = [
            'one',
            'two'
        ];
        $booking->getReservationId()->willReturn('123');
        $booking->getComments()->willReturn('previous');
        $jarvisHttpClient->getBookingDetails('123')->willReturn($response)->shouldNotBeCalled();
        $booking->setComments('previous | one | two')->willReturn($booking)->shouldNotBeCalled();
        $this->process($booking)->shouldBe($booking);
    }

    function it_not_response_from_jarvis(
        Booking $booking,
        JarvisClient $jarvisHttpClient,
        \stdClass $response
    )
    {
        $booking->getComponents()->willReturn([]);
        $booking->isConfirmed()->willReturn(true);
        $response->experience = new \stdClass();
        $response->experience->components = [
            'one',
            'two'
        ];
        $booking->getReservationId()->willReturn('123');
        $booking->getComments()->willReturn('previous');
        $jarvisHttpClient->getBookingDetails('123')->willThrow(\Exception::class);
        $booking->setComments('previous | one | two')->willReturn($booking)->shouldNotBeCalled();
        $this->process($booking)->shouldBe($booking);
    }

    function it_jarvis_no_experience_components(
        Booking $booking,
        JarvisClient $jarvisHttpClient,
        \stdClass $response
    )
    {
        $booking->getComponents()->willReturn([]);
        $booking->isConfirmed()->willReturn(true);
        $response->experience = new \stdClass();
        $response->experience->components = null;
        $booking->getReservationId()->willReturn('123');
        $jarvisHttpClient->getBookingDetails('123')->willReturn($response);
        $booking->setComments('previous | one | two')->willReturn($booking)->shouldNotBeCalled();
        $this->process($booking)->shouldBe($booking);
    }

    function it_jarvis_no_experience(
        Booking $booking,
        JarvisClient $jarvisHttpClient,
        \stdClass $response
    )
    {
        $booking->getComponents()->willReturn([]);
        $booking->isConfirmed()->willReturn(true);
        $response->experience = null;
        $booking->getReservationId()->willReturn('123');
        $jarvisHttpClient->getBookingDetails('123')->willReturn($response);
        $booking->setComments('previous | one | two')->willReturn($booking)->shouldNotBeCalled();
        $this->process($booking)->shouldBe($booking);
    }
}

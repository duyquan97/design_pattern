<?php

namespace spec\App\Service\ChannelManager\SoapOta\Serializer\V2007B;

use App\Service\ChannelManager\SoapOta\Serializer\V2007B\PushBookingNormalizer;
use App\Service\ChannelManager\SoapOta\Serializer\V2007B\BookingNormalizer;
use App\Model\PushBooking;
use App\Entity\Booking;
use App\Entity\Partner;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PushBookingNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PushBookingNormalizer::class);
    }

    function let(BookingNormalizer $bookingNormalizer)
    {
        $this->beConstructedWith($bookingNormalizer);
    }

    function it_normalizes_a_valid_object(BookingNormalizer $bookingNormalizer, PushBooking $pushBooking, Booking $booking, Partner $partner)
    {
        $pushBooking->getBooking()->willReturn($booking);
        $booking->getStatus()->willReturn('Commit');
        $booking->getPartner()->willReturn($partner);
        $partner->getIdentifier()->willReturn('hotel');

        $bookingNormalizer->normalize($booking)->willReturn('booking');

        $this->normalize($pushBooking, ['token' => '123'])->shouldBe([
            'EchoToken' => '123',
            'ResStatus' => 'Commit',
            'POS' => [
                'Source' => [
                    'RequestorID' => [
                        'Type' => 22,
                        'ID' => 'SBX',
                    ],
                    'BookingChannel' => [
                        'Primary' => true,
                        'CompanyName' => [
                            'Code' => 'SBX',
                            '_' => 'Smartbox Standard Rate',
                        ],
                    ],
                ],
            ],
            'Inventories' => [
                'HotelCode' => 'hotel',
            ],
            'HotelReservations' => [
                'HotelReservation' => 'booking',
            ],
        ]);
    }

    function it_denormalizes_a_valid_object(PushBooking $pushBooking)
    {
        $this->denormalize([])->shouldBe(null);
    }

    function it_supports_normalization_for()
    {
        $this->supportsNormalization('App\Model\PushBooking')->shouldBe(true);
    }

    function it_supports_denormalization_for()
    {
        $this->supportsDenormalization('App\Model\PushBooking')->shouldBe(false);
    }
}

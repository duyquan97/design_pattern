<?php

namespace spec\App\Service\ChannelManager\SoapOta\Serializer\V2007B;

use App\Service\ChannelManager\SoapOta\Serializer\V2007B\BookingCollectionNormalizer;
use App\Service\ChannelManager\SoapOta\Serializer\V2007B\BookingNormalizer;
use App\Model\BookingCollection;
use App\Entity\Booking;
use App\Entity\Partner;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BookingCollectionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingCollectionNormalizer::class);
    }

    function let(BookingNormalizer $bookingNormalizer)
    {
        $this->beConstructedWith($bookingNormalizer);
    }

    function it_normalizes_a_valid_object(BookingNormalizer $bookingNormalizer, BookingCollection $bookingCollection, Booking $booking, Partner $partner)
    {
        $bookingCollection->getBookings()->willReturn([$booking]);
        $bookingNormalizer->normalize($booking)->willReturn('booking');
        $partner->getIdentifier()->willReturn('hotel');

        $this->normalize($bookingCollection)->shouldBe([
            'ReservationsList' => [
                'HotelReservation' => [
                    'booking'
                ],
            ],
        ]);
    }

    function it_denormalizes_a_valid_object(BookingCollection $bookingCollection)
    {
        $this->denormalize([])->shouldBe(null);
    }

    function it_supports_normalization_for()
    {
        $this->supportsNormalization('App\Model\BookingCollection')->shouldBe(true);
    }

    function it_supports_denormalization_for()
    {
        $this->supportsDenormalization('App\Model\BookingCollection')->shouldBe(false);
    }
}

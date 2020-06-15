<?php

namespace spec\App\Service\ChannelManager\Wubook\Serializer;

use App\Entity\Booking;
use App\Model\BookingCollection;
use App\Model\BookingInterface;
use App\Model\PartnerInterface;
use App\Service\ChannelManager\Wubook\Serializer\BookingCollectionNormalizer;
use App\Service\ChannelManager\Wubook\Serializer\BookingNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

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

    function it_doesnt_denormalize()
    {
        $request = [
            "start_time" => "2014-04-25 15:00:00"
        ];

        $this->shouldThrow(MethodNotImplementedException::class)->during('denormalize', [json_encode($request), []]);
    }

    function it_normalizes_valid_data(BookingCollection $bookingCollection, BookingNormalizer $bookingNormalizer, BookingInterface $booking, BookingInterface $booking1, PartnerInterface $partner)
    {
        $dataBooking = ["booking" => ""];
        $dataBooking1 = ["booking1" => ""];

        $returnBookingNormalizer = [
            $dataBooking,
            $dataBooking1,
        ];

        $return = [
            "bookings" => $returnBookingNormalizer
        ];

        $bookingCollection->addBooking($booking);
        $bookingCollection->addBooking($booking1);

        $bookingCollection->getBookings()->willReturn([$booking, $booking1]);
        $bookingNormalizer->normalize($booking, ['partner' => $partner])->shouldBeCalled()->willReturn($dataBooking);
        $bookingNormalizer->normalize($booking1, ['partner' => $partner])->shouldBeCalled()->willReturn($dataBooking1);

        $this->normalize($bookingCollection, ['partner' => $partner])->shouldBeLike($return);
    }

    function it_supports_normalization_for()
    {
        $this->supportsNormalization(BookingCollection::class)->shouldBe(true);
    }

    function it_doesnt_support_denormalization_for()
    {
        $this->supportsDenormalization(BookingCollection::class)->shouldBe(false);
    }

    function it_doesnt_support_normalization_for()
    {
        $this->supportsNormalization(Booking::class)->shouldBe(false);
    }
}

<?php

namespace spec\App\Service\Iresa\Serializer;

use App\Model\Booking;
use App\Model\BookingCollection;
use App\Model\Factory\BookingCollectionFactory;
use App\Model\PartnerInterface;
use App\Service\Iresa\Serializer\BookingCollectionNormalizer;
use App\Service\Iresa\Serializer\BookingNormalizer;
use PhpSpec\ObjectBehavior;

class BookingCollectionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingCollectionNormalizer::class);
    }

    function let(
        BookingCollectionFactory $bookingCollectionFactory,
        BookingNormalizer $bookingNormalizer
    )
    {
        $this->beConstructedWith($bookingCollectionFactory, $bookingNormalizer);
    }

    function it_denormalizes_to_booking_collection_object(PartnerInterface $partner, BookingCollection $bookingCollection, Booking $booking, Booking $booking1, BookingCollectionFactory $bookingCollectionFactory, BookingNormalizer $bookingNormalizer)
    {
        $bookingCollectionFactory->create()->willReturn($bookingCollection);

        $context = ['partner' => $partner];
        $b1 = (object) [
            'booking_data' => '1'
        ];

        $b2 = (object) [
            'booking_data' => '2'
        ];

        $data = [$b1, $b2];

        $bookingNormalizer->denormalize($b1, $context)->willReturn($booking);
        $bookingNormalizer->denormalize($b2, $context)->willReturn($booking1);
        $bookingCollection->addBooking($booking)->shouldBeCalled();
        $bookingCollection->addBooking($booking1)->shouldBeCalled();

        $this->denormalize($data, $context)->shouldBe($bookingCollection);
    }

    function it_does_not_support_normalization()
    {
        $this->supportsNormalization(BookingCollection::class)->shouldBe(false);
    }

    function it_only_supports_booking_denormalization()
    {
        $this->supportsDenormalization(BookingCollection::class)->shouldBe(true);
        $this->supportsDenormalization(\stdClass::class)->shouldBe(false);
    }
}

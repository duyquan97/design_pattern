<?php

namespace spec\App\Service\ChannelManager\Wubook\Serializer;

use App\Entity\Booking;
use App\Model\BookingCollection;
use App\Model\BookingInterface;
use App\Model\BookingProductInterface;
use App\Booking\Model\BookingStatus;
use App\Model\GuestInterface;
use App\Model\PartnerInterface;
use App\Model\WubookBookingStatus;
use App\Service\ChannelManager\Wubook\Serializer\BookingNormalizer;
use App\Service\ChannelManager\Wubook\Serializer\BookingProductNormalizer;
use App\Service\ChannelManager\Wubook\Serializer\GuestNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

class BookingNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingNormalizer::class);
    }

    function let(BookingProductNormalizer $bookingProductNormalizer, GuestNormalizer $guestCollectionNormalizer)
    {
        $this->beConstructedWith($bookingProductNormalizer, $guestCollectionNormalizer);
    }

    function it_doesnt_denormalize()
    {
        $request = [
            "start_time" => "2014-04-25 15:00:00"
        ];

        $this->shouldThrow(MethodNotImplementedException::class)->during('denormalize', [json_encode($request), []]);
    }

    function it_normalizes_valid_data(
        BookingInterface $booking,
        PartnerInterface $partner,
        GuestNormalizer $guestCollectionNormalizer,
        GuestInterface $guest,
        GuestInterface $guest2,
        BookingProductInterface $bookingProduct,
        BookingProductNormalizer $bookingProductNormalizer
    )
    {
        $returnBookingNormalizer = [
            "booking_id" => "123",
            "status" => WubookBookingStatus::NEW,
            "created" => "2014-04-27 01:12:28",
            "modified" => "2014-04-27 01:12:28",
            "utc_offset" => "+0000",
            "hotel_id" => "12",
            "currency" => "EUR",
            "arrival_date" => "2014-05-01",
            "departure_date" => "2014-05-03",
            "arrival_hour" => "11:00",
            "departure_hour" => "10:00",
            "rooms" => [["room_id" => "1"]],
            "customer" => ["first_name" => "John"],
            "notes" => "package",
            "total_price" => 600,
        ];

        $booking->getPartner()->willReturn($partner);
        $booking->getReservationId()->willReturn("123");
        $booking->getStatus()->willReturn(BookingStatus::CONFIRMED);
        $booking->isConfirmed()->willReturn(true);

        $booking->getCreatedAt()->willReturn(new \DateTime('2014-04-27 01:12:28'));

        $booking->getUpdatedAt()->willReturn(new \DateTime('2014-04-27 01:12:28'));

        $partner->getIdentifier()->willReturn("12");
        $booking->getCurrency()->willReturn('EUR');

        $booking->getStartDate()->willReturn(new \DateTime('2014-05-01 11:00'));

        $booking->getEndDate()->willReturn(new \DateTime('2014-05-03 10:00'));

        $booking->getComments()->willReturn("package");
        $booking->getTotalAmount()->willReturn(600);

        $booking->getGuests()->willReturn([$guest, $guest2]);
        $guest->isMain()->willReturn(true);
        $guestCollectionNormalizer->normalize($guest)->willReturn(["first_name" => "John"]);

        $booking->getBookingProducts()->willReturn([$bookingProduct]);

        $bookingProductNormalizer->normalize($bookingProduct)->willReturn(["room_id" => "1"]);

        $this->normalize($booking, ['partner' => $partner])->shouldBeLike($returnBookingNormalizer);
    }

    function it_normalizes_cancel_data(
        BookingInterface $booking,
        PartnerInterface $partner,
        GuestNormalizer $guestCollectionNormalizer,
        GuestInterface $guest,
        GuestInterface $guest2,
        BookingProductInterface $bookingProduct,
        BookingProductNormalizer $bookingProductNormalizer
    )
    {
        $returnBookingNormalizer = [
            "booking_id" => "123",
            "status" => WubookBookingStatus::CANCEL,
            "created" => "2014-04-27 01:12:28",
            "modified" => "2014-04-27 01:12:28",
            "utc_offset" => "+0000",
            "hotel_id" => "12",
            "currency" => "EUR",
            "arrival_date" => "2014-05-01",
            "departure_date" => "2014-05-03",
            "arrival_hour" => "11:00",
            "departure_hour" => "10:00",
            "rooms" => [["room_id" => "1"]],
            "customer" => ["first_name" => "John"],
            "notes" => "package",
            "total_price" => 600,
        ];

        $booking->getPartner()->willReturn($partner);
        $booking->getReservationId()->willReturn("123");
        $booking->getStatus()->willReturn(BookingStatus::CANCELLED);
        $booking->isConfirmed()->willReturn(false);

        $booking->getCreatedAt()->willReturn(new \DateTime('2014-04-27 01:12:28'));

        $booking->getUpdatedAt()->willReturn(new \DateTime('2014-04-27 01:12:28'));

        $partner->getIdentifier()->willReturn("12");
        $booking->getCurrency()->willReturn('EUR');

        $booking->getStartDate()->willReturn(new \DateTime('2014-05-01 11:00'));

        $booking->getEndDate()->willReturn(new \DateTime('2014-05-03 10:00'));

        $booking->getComments()->willReturn("package");
        $booking->getTotalAmount()->willReturn(600);

        $booking->getGuests()->willReturn([$guest, $guest2]);
        $guest->isMain()->willReturn(true);
        $guestCollectionNormalizer->normalize($guest)->willReturn(["first_name" => "John"]);

        $booking->getBookingProducts()->willReturn([$bookingProduct]);

        $bookingProductNormalizer->normalize($bookingProduct)->willReturn(["room_id" => "1"]);

        $this->normalize($booking, ['partner' => $partner])->shouldBeLike($returnBookingNormalizer);
    }

    function it_supports_normalization_for()
    {
        $this->supportsNormalization(Booking::class)->shouldBe(true);
    }

    function it_doesnt_support_denormalization_for()
    {
        $this->supportsDenormalization(Booking::class)->shouldBe(false);
    }

    function it_doesnt_support_normalization_for()
    {
        $this->supportsNormalization(BookingCollection::class)->shouldBe(false);
    }
}

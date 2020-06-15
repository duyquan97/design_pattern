<?php

namespace spec\App\Service\ChannelManager\SoapOta\Serializer\V2007B;

use App\Entity\BookingProduct;
use App\Entity\Guest;
use App\Model\Booking;
use App\Booking\Model\BookingStatus;
use App\Service\ChannelManager\SoapOta\Serializer\V2007B\BookingNormalizer;
use App\Service\ChannelManager\SoapOta\Serializer\V2007B\BookingProductNormalizer;
use App\Service\ChannelManager\SoapOta\Serializer\V2007B\GuestNormalizer;
use PhpSpec\ObjectBehavior;

class BookingNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingNormalizer::class);
    }

    function let(GuestNormalizer $guestNormalizer, BookingProductNormalizer $bookingProductNormalizer)
    {
        $this->beConstructedWith($guestNormalizer, $bookingProductNormalizer);
    }

    function it_normalizes_a_valid_object(GuestNormalizer $guestNormalizer, BookingProductNormalizer $bookingProductNormalizer, Booking $booking, Guest $guest, BookingProduct $bookingProduct)
    {
        $booking->getGuests()->willReturn([$guest]);
        $booking->getBookingProducts()->willReturn([$bookingProduct]);

        $guestNormalizer->normalize($guest, ['index' => 1])->willReturn('guest');
        $bookingProductNormalizer->normalize($bookingProduct)->willReturn('bookingProduct');

        $booking->getStatus()->willReturn(BookingStatus::CONFIRMED);
        $booking->getReservationId()->willReturn('123abc');
        $booking->getTotalAmount()->willReturn(123456789.00);
        $booking->getCurrency()->willReturn('EUR');
        $booking->getRequests()->willReturn('requests');
        $booking->getComments()->willReturn('comments');

        $booking->getCreatedAt()->willReturn(\DateTime::createFromFormat('!d-m-Y', '15-12-2018', new \DateTimeZone('UTC')));
        $booking->getUpdatedAt()->willReturn(\DateTime::createFromFormat('!d-m-Y', '15-12-2018', new \DateTimeZone('UTC')));
        $booking->getStartDate()->willReturn(\DateTime::createFromFormat('d-m-Y', '20-12-2018', new \DateTimeZone('UTC')));
        $booking->getEndDate()->willReturn(\DateTime::createFromFormat('d-m-Y', '21-12-2018', new \DateTimeZone('UTC')));

        $this->normalize($booking)->shouldBe([
            'RoomStayReservation' => true,
            'ResStatus'           => BookingStatus::CONFIRMED,
            'CreateDateTime'      => '2018-12-15T00:00:00+00:00',
            'LastModifyDateTime'  => '2018-12-15T00:00:00+00:00',
            'UniqueID'            => [
                'Type' => 14,
                'ID'   => '123abc',
            ],
            'ResGlobalInfo'       => [
                'HotelReservationIDs' => [
                    'HotelReservationID' => [
                        [
                            'ResID_Value' => '123abc',
                        ],
                    ],
                ],
                'TimeSpan'            => [
                    'Start'    => '2018-12-20',
                    'End'      => '2018-12-21',
                    'Duration' => '1',
                ],
                'Total'               => [
                    'AmountAfterTax' => 123456789.00,
                    'CurrencyCode'   => 'EUR',
                ],
                'SpecialRequests'     => [
                    [
                        'Text' => 'requests',
                    ],
                ],
                'Comments'            => [
                    [
                        'Text' => 'comments',
                    ],
                ],
            ],
            'RoomStays'           => [
                'RoomStay' => ['bookingProduct'],
            ],
            'ResGuests'           => [
                'ResGuest' => ['guest'],
            ],
        ]);
    }

    function it_denormalizes_a_valid_object(Booking $booking)
    {
        $this->denormalize([])->shouldBe(null);
    }

    function it_supports_normalization_for()
    {
        $this->supportsNormalization(Booking::class)->shouldBe(true);
    }

    function it_supports_denormalization_for()
    {
        $this->supportsDenormalization(Booking::class)->shouldBe(false);
    }
}

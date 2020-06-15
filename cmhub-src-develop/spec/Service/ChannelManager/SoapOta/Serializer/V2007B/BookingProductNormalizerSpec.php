<?php

namespace spec\App\Service\ChannelManager\SoapOta\Serializer\V2007B;

use App\Entity\Booking;
use App\Entity\Partner;
use App\Entity\Product;
use App\Entity\ProductRate as Rate;
use App\Model\BookingProduct;
use App\Model\GuestInterface;
use App\Service\ChannelManager\SoapOta\Serializer\V2007B\BookingProductNormalizer;
use App\Service\ChannelManager\SoapOta\Serializer\V2007B\RateNormalizer;
use PhpSpec\ObjectBehavior;

class BookingProductNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingProductNormalizer::class);
    }

    function let(RateNormalizer $rateNormalizer)
    {
        $this->beConstructedWith($rateNormalizer);
    }

    function it_normalizes_a_valid_object(RateNormalizer $rateNormalizer, Booking $booking, Partner $partner, Product $product, Rate $rate, BookingProduct $bookingProduct, BookingProduct $bookingProduct1, GuestInterface $guest, GuestInterface $guest1)
    {
        $bookingProduct->getRates()->willReturn([$rate]);
        $rateNormalizer->normalize($rate)->willReturn(['rate']);

        $bookingProduct->getGuests()->willReturn([$guest1]);
        $guest->getBookingProduct()->willReturn($bookingProduct1);
        $guest1->getBookingProduct()->willReturn($bookingProduct);
        $bookingProduct->getBooking()->willReturn($booking);
        $booking->getGuests()->willReturn([$guest, $guest1]);
        $booking->getStartDate()->willReturn(\DateTime::createFromFormat('d-m-Y', '15-12-2018'));
        $booking->getEndDate()->willReturn(\DateTime::createFromFormat('d-m-Y', '18-12-2018'));

        $partner->getIdentifier()->willReturn('hotel1');
        $partner->getName()->willReturn('hotel');
        $bookingProduct->getCurrency()->willReturn('EUR');

        $bookingProduct->getProduct()->willReturn($product);
        $product->getIdentifier()->willReturn('123abc');
        $product->getPartner()->willReturn($partner);

        $bookingProduct->getAmount()->willReturn(123456);
        $bookingProduct->getTotalGuests()->willReturn(1);


        $this->normalize($bookingProduct)->shouldBeLike([
            'RoomTypes' => [
                'RoomType' => [
                    [
                        'RoomTypeCode' => '123abc',
                        'NumberOfUnits' => 1,
                    ],
                ],
            ],
            'RoomRates' => [
                'RoomRate' => [
                    'RatePlanCode' => 'SBX',
                    'RoomTypeCode' => '123abc',
                    'Rates' => ['rate'],
                    'NumberOfUnits' => 1,
                ],
            ],
            'Total' => [
                'AmountAfterTax' => 123456,
                'CurrencyCode' => 'EUR',
            ],
            'BasicPropertyInfo' => [
                'HotelCode' => 'hotel1',
                'HotelName' => 'hotel',
            ],
            'GuestCounts' => [
                'GuestCount' => [
                    'AgeQualifyingCode' => 10,
                    'Count' => 1,
                ],
            ],
            'TimeSpan' => [
                'Start' => '2018-12-15',
                'End' => '2018-12-18',
            ],
            'Guarantee' => [
                'GuaranteeType' => 'PrePay',
            ],
            'ResGuestRPHs' => [
                [
                    'RPH' => '2',
                ],
            ],
        ]);
    }

    function it_denormalizes_a_valid_object(BookingProduct $bookingProduct)
    {
        $this->denormalize([])->shouldBe(null);
    }

    function it_supports_normalization_for()
    {
        $this->supportsNormalization('App\Model\BookingProduct')->shouldBe(true);
    }

    function it_supports_denormalization_for()
    {
        $this->supportsDenormalization('App\Model\BookingProduct')->shouldBe(false);
    }
}

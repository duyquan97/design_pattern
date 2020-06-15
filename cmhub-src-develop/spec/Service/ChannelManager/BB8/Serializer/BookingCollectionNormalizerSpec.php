<?php

namespace spec\App\Service\ChannelManager\BB8\Serializer;

use App\Entity\Booking;
use App\Model\BookingCollection;
use App\Model\BookingInterface;
use App\Model\BookingProductInterface;
use App\Model\PartnerInterface;
use App\Model\ProductInterface;
use App\Model\RateInterface;
use App\Model\RatePlanCode;
use App\Service\ChannelManager\BB8\Serializer\BookingCollectionNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

class BookingCollectionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingCollectionNormalizer::class);
    }

    function it_doesnt_denormalize()
    {
        $request = [
            "start_time" => "2014-04-25 15:00:00"
        ];

        $this->shouldThrow(MethodNotImplementedException::class)->during('denormalize', [json_encode($request), []]);
    }

    function it_normalizes_valid_data(
        BookingCollection $bookingCollection,
        BookingInterface $booking,
        PartnerInterface $partner,
        BookingProductInterface $bookingProduct,
        \DateTime $dateTime,
        \DateTime $dateTime1,
        \DateTime $dateTime2,
        RateInterface $rate,
        ProductInterface $product
    )
    {

        $return = [
            [
                'externalId' => '123245',
                'dates' => [
                    [
                        'date'               => '2019-09-30',
                        'externalRateBandId' => RatePlanCode::SBX,
                        'externalRoomId'     => '3234234',
                    ]
                ],
                'bookingStatus' => 'status',
                'bookingType' => 'instant',
                'externalPartnerId' => '3324614',
                'bookingStart' => '2019-09-30',
                'bookingEnd' => '2019-10-01',
                'externalUpdatedFrom' => '2019-11-28T04:16:05+00:00',
            ]
        ];

        $bookingCollection->getBookings()->shouldBeCalled()->willReturn([$booking]);
        $booking->getBookingProducts()->shouldBeCalled()->willReturn([$bookingProduct]);
        $bookingProduct->getRates()->shouldBeCalled()->willReturn([$rate]);
        $rate->getStart()->shouldBeCalled()->willReturn($dateTime2);
        $dateTime2->format('Y-m-d')->willReturn('2019-09-30');
        $rate->getProduct()->shouldBeCalled()->willReturn($product);
        $product->getIdentifier()->willReturn('3234234');

        $booking->getReservationId()->willReturn('123245');
        $booking->getStatus()->willReturn('status');
        $booking->getUpdatedAt()->willReturn(date_create('2019-11-28T04:16:05+00:00'));

        $booking->getStartDate()->shouldBeCalled()->willReturn($dateTime);
        $dateTime->format('Y-m-d')->shouldBeCalled()->willReturn('2019-09-30');

        $booking->getEndDate()->shouldBeCalled()->willReturn($dateTime1);
        $dateTime1->format('Y-m-d')->shouldBeCalled()->willReturn('2019-10-01');

        $booking->getPartner()->willReturn($partner);
        $partner->getIdentifier()->willReturn('3324614');

        $this->normalize($bookingCollection)->shouldBeLike($return);
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

<?php

namespace spec\App\Service\Iresa\Serializer;

use App\Entity\Partner;
use App\Model\Booking;
use App\Model\BookingProduct;
use App\Model\Factory\BookingFactory;
use App\Service\Iresa\Serializer\BookingNormalizer;
use App\Service\Iresa\Serializer\BookingProductNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BookingNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingNormalizer::class);
    }

    function let(BookingProductNormalizer $bookingProductNormalizer, BookingFactory $bookingFactory)
    {
        $this->beConstructedWith($bookingProductNormalizer, $bookingFactory);
    }

    function it_denormalizes_to_booking_object(BookingProductNormalizer $bookingProductNormalizer, BookingFactory $bookingFactory, Booking $booking, BookingProduct $bookingProduct, BookingProduct $bookingProduct1, Partner $partner)
    {
        $partner->getCurrency()->willReturn('EUR');
        $context = ['partner' => $partner];
        $data = json_decode(
            json_encode(
                [
                    'status'           => 'status',
                    'dateStart'        => '2018-01-01',
                    'dateEnd'          => '2018-01-12',
                    'dateCreation'     => '2017-12-01',
                    'dateLastModified' => '2017-12-01',
                    'totalAmount'      => 12.12,
                    'currency'         => 'EUR',
                    'reservationId'    => '1231',
                    'roomTypes'        => [
                        [
                            'room' => 'details'
                        ],
                        [
                            'room' => 'details2'
                        ]
                    ]
                ]
            )
        );

        $bookingFactory->create()->willReturn($booking);
        $booking->setStatus('status')->shouldBeCalled()->willReturn($booking);
        $booking->setTotalAmount(12.12)->willReturn($booking);
        $booking->setCurrency('EUR')->shouldBeCalled()->willReturn($booking);
        $booking->setReservationId('1231')->shouldBeCalled()->willReturn($booking);
        $booking
            ->setStartDate(
                Argument::that(function (\DateTime $start) {
                    return $start->format('Y-m-d') === '2018-01-01';
                })
            )
            ->willReturn($booking);
        $booking
            ->setEndDate(
                Argument::that(function (\DateTime $end) {
                    return $end->format('Y-m-d') === '2018-01-12';
                })
            )
            ->willReturn($booking);
        $booking
            ->setCreatedAt(
                Argument::that(function (\DateTime $created) {
                    return $created->format('Y-m-d') === '2017-12-01';
                })
            )
            ->willReturn($booking);

        $booking
            ->setUpdatedAt(
                Argument::that(function (\DateTime $updated) {
                    return $updated->format('Y-m-d') === '2017-12-01';
                })
            )
            ->willReturn($booking);


        $bookingProductNormalizer
            ->denormalize(
                (object) [
                    'room' => 'details'
                ],
                $context
            )
            ->shouldBeCalled()
            ->willReturn($bookingProduct);

        $bookingProductNormalizer
            ->denormalize(
                (object) [
                    'room' => 'details2'
                ],
                $context
            )
            ->shouldBeCalled()
            ->willReturn($bookingProduct1);

        $booking->addBookingProduct($bookingProduct);
        $bookingProduct->setBooking($booking)->shouldBeCalled();
        $booking->addBookingProduct($bookingProduct1);
        $bookingProduct1->setBooking($booking)->shouldBeCalled();

        $this->denormalize($data, $context)->shouldBe($booking);
    }

    function it_only_supports_booking_object_denormalization()
    {
        $this->supportsDenormalization(Booking::class)->shouldBe(true);
        $this->supportsDenormalization(\stdClass::class)->shouldBe(false);
    }

    function it_does_not_support_normalization()
    {
        $this->supportsNormalization(Booking::class)->shouldBe(false);
        $this->supportsNormalization(\stdClass::class)->shouldBe(false);
    }
}

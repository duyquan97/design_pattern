<?php

namespace spec\App\Service\ChannelManager\Wubook\Serializer;

use App\Entity\Booking;
use App\Entity\BookingProduct;
use App\Entity\Guest;
use App\Entity\ProductRate;
use App\Model\BookingProductInterface;
use App\Model\GuestInterface;
use App\Model\PartnerInterface;
use App\Model\ProductInterface;
use App\Model\RateInterface;
use App\Service\ChannelManager\Wubook\Serializer\BookingProductNormalizer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

class BookingProductNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingProductNormalizer::class);
    }

    function let()
    {
        $this->beConstructedWith();
    }

    function it_doesnt_denormalize()
    {
        $request = [
            "start_time" => "2014-04-25 15:00:00"
        ];

        $this->shouldThrow(MethodNotImplementedException::class)->during('denormalize', [json_encode($request), []]);
    }

    function it_normalizes_valid_data(Collection $guestCollection, BookingProductInterface $bookingProduct, ProductRate $rate, ProductRate $rate1, Guest $guest, Guest $guest1, PartnerInterface $partner, ProductInterface $product)
    {
        $response = [
            'room_id' => "123",
            'daily_prices' => [
                '2019-05-02' => [
                    'price' => 12,
                    'rate_id' => "SBX"
                ],
                '2019-05-03' => [
                    'price' => 12,
                    'rate_id' => "SBX"
                ]
            ],
            'adults_number' => 2,
            'guests' => [
                "John Doe",
                "Jane Doe"
            ],
        ];

        $bookingProduct->getProduct()->willReturn($product);
        $product->getIdentifier()->willReturn("123");

        $rate->getStart()->willReturn(new \DateTime("2019-05-02"));
        $rate1->getStart()->willReturn(new \DateTime("2019-05-03"));

        $bookingProduct->getRates()->willReturn([$rate, $rate1]);

        $rate->getAmount()->willReturn(12);
        $rate1->getAmount()->willReturn(12);

        $bookingProduct->getTotalGuests()->willReturn(2);

        $bookingProduct->getGuests()->willReturn($guestCollection);
        $guestCollection->toArray()->willReturn([$guest, $guest1]);

        $guest->getName()->willReturn("John Doe");
        $guest1->getName()->willReturn("Jane Doe");

        $this->normalize($bookingProduct, ['partner' => $partner])->shouldBeLike($response);
    }

    function it_supports_normalization_for()
    {
        $this->supportsNormalization(BookingProduct::class)->shouldBe(true);
    }

    function it_doesnt_support_denormalization_for()
    {
        $this->supportsDenormalization(BookingProduct::class)->shouldBe(false);
    }

    function it_doesnt_support_normalization_for()
    {
        $this->supportsNormalization(Booking::class)->shouldBe(false);
    }
}

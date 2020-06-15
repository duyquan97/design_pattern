<?php

namespace spec\App\Service\Iresa\Serializer;

use App\Entity\ChannelManager;
use App\Model\BookingProduct;
use App\Model\Factory\BookingProductFactory;
use App\Model\Guest;
use App\Model\PartnerInterface;
use App\Model\ProductInterface;
use App\Model\Rate;
use App\Service\Iresa\Serializer\BookingProductNormalizer;
use App\Service\Iresa\Serializer\GuestNormalizer;
use App\Service\Iresa\Serializer\RateNormalizer;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BookingProductNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingProductNormalizer::class);
    }

    function let(
        BookingProductFactory $bookingProductFactory,
        GuestNormalizer $guestNormalizer,
        RateNormalizer $rateNormalizer,
        ProductLoader $productLoader,
        CmhubLogger $logger
    )
    {
        $this->beConstructedWith($bookingProductFactory, $guestNormalizer, $rateNormalizer, $productLoader, $logger);
    }

    function it_denormalizes_booking_product(
        BookingProductFactory $bookingProductFactory,
        GuestNormalizer $guestNormalizer,
        RateNormalizer $rateNormalizer,
        ProductLoader $productLoader,
        PartnerInterface $partner,
        ProductInterface $product,
        BookingProduct $bookingProduct,
        Guest $guest,
        Guest $guest1,
        Rate $rate,
        Rate $rate1
    )
    {
        $partner->getCurrency()->willReturn('EUR');
        $productLoader->find($partner, 123, [])->willReturn($product);
        $bookingProductFactory->create($product)->willReturn($bookingProduct);
        $bookingProduct->setAmount(12.2)->shouldBeCalled()->willReturn($bookingProduct);
        $bookingProduct->setCurrency('EUR')->shouldBeCalled()->willReturn($bookingProduct);

        $guestNormalizer->denormalize($guestData = (object) ['the' => 'guest'])->shouldBeCalled()->willReturn($guest);
        $bookingProduct->addGuest($guest)->shouldBeCalled();

        $guestNormalizer->denormalize($guestData1 = (object) ['the' => 'guest1'])->shouldBeCalled()->willReturn($guest1);
        $bookingProduct->addGuest($guest1)->shouldBeCalled();

        $rateNormalizer->denormalize($rateData = (object) ['the' => 'rate'], ['product' => $product])->shouldBeCalled()->willReturn($rate);
        $bookingProduct->addRate($rate)->shouldBeCalled();

        $rateNormalizer->denormalize($rateData1 = (object) ['the' => 'rate1'], ['product' => $product])->shouldBeCalled()->willReturn($rate1);
        $bookingProduct->addRate($rate1)->shouldBeCalled();

        $this->denormalize(
            (object) [
                'roomTypeCode' => 123,
                'totalAmount'  => 12.2,
                'currency'     => 'EUR',
                'guests'       => [
                    $guestData,
                    $guestData1
                ],
                'rates'        => [
                    $rateData,
                    $rateData1
                ]
            ],
            ['partner' => $partner]
        );
    }

    function it_returns_null_if_product_not_found(
        ProductLoader $productLoader,
        PartnerInterface $partner,
        CmhubLogger $logger,
        ChannelManager $channelManager
    )
    {
        $partner->getIdentifier()->willReturn('partner_id');
        $partner->getUsername()->willReturn('partner_id');
        $partner->getChannelManager()->willReturn($channelManager);
        $productLoader->find($partner, 123, [])->willReturn();
        $logger->addRecord(\Monolog\Logger::INFO, Argument::type('string'), Argument::type('array'), $this)->shouldBeCalled();
        $this->denormalize((object) ['roomTypeCode' => 123], ['partner' => $partner])->shouldBe(null);
    }

    function it_only_denormalizes_booking_product()
    {
        $this->supportsDenormalization(BookingProduct::class)->shouldBe(true);
        $this->supportsDenormalization(\stdClass::class)->shouldBe(false);
    }

    function it_does_not_support_normalization()
    {
        $this->supportsNormalization(BookingProduct::class)->shouldBe(false);
        $this->supportsNormalization(\stdClass::class)->shouldBe(false);
    }
}

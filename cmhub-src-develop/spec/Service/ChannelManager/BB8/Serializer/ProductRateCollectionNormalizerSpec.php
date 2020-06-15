<?php

namespace spec\App\Service\ChannelManager\BB8\Serializer;

use App\Entity\Product;
use App\Model\Factory\ProductRateCollectionFactory;
use App\Model\Factory\RateFactory;
use App\Model\ProductRate;
use App\Entity\Partner;
use App\Model\ProductRateCollection;
use App\Model\Rate;
use App\Service\ChannelManager\BB8\Serializer\ProductRateCollectionNormalizer;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductRateCollectionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRateCollectionNormalizer::class);
    }

    function let(
        ProductRateCollectionFactory $productRateCollectionFactory,
        RateFactory $rateFactory,
        PartnerLoader $partnerLoader,
        ProductLoader $productLoader
    )
    {
        $this->beConstructedWith($productRateCollectionFactory, $rateFactory, $partnerLoader, $productLoader);
    }

    function it_normalize(
        ProductRate $productRate,
        Rate $rate,
        Product $product,
        Partner $partner
    ) {
        $expected = [
            [
                'currencyCode' => 'EUR',
                'date' => '2019-03-20',
                'amount' => (float) 9990,
                'rateBandCode' => 'SBX',
                'externalPartnerId' => '123ABC',
                'externalRoomId' => '123ABC',
                'externalCreatedAt' => '2019-03-20T00:00:00+00:00',
                'externalUpdatedAt' => '2019-03-20T00:00:00+00:00',
            ],
        ];

        $productRate->getRates()->shouldBeCalled()->willReturn([$rate]);
        $productRate->getProduct()->shouldBeCalled()->willReturn($product);
        $product->getPartner()->shouldBeCalled()->willReturn($partner);

        $partner->getCurrency()->willReturn('EUR');
        $rate->getStart()->willReturn(\DateTime::createFromFormat('Y-m-d', '2019-03-20'));
        $rate->getAmount()->willReturn('99.9');
        $product->getIdentifier()->willReturn('123ABC');
        $partner->getIdentifier()->willReturn('123ABC');

        $product
            ->getCreatedAt()
            ->shouldBeCalled()
            ->willReturn(new \DateTime('2019-03-20', new \DateTimeZone('UTC')))
        ;

        $product
            ->getUpdatedAt()
            ->shouldBeCalled()
            ->willReturn(new \DateTime('2019-03-20', new \DateTimeZone('UTC')))
        ;

        $this->normalize([$productRate])->shouldBe($expected);
    }

    function it_support_normalization()
    {
        $this->supportsNormalization(ProductRateCollection::class)->shouldBe(true);
    }

    function it_does_not_support_denormalization()
    {
        $this->supportsDenormalization(Argument::type('string'))->shouldBe(false);
    }
}

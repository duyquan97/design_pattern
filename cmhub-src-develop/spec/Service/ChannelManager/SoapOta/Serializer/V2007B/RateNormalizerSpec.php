<?php

namespace spec\App\Service\ChannelManager\SoapOta\Serializer\V2007B;

use App\Entity\Partner;
use App\Entity\Product;
use App\Model\Rate;
use App\Service\ChannelManager\SoapOta\Serializer\V2007B\RateNormalizer;
use PhpSpec\ObjectBehavior;

class RateNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RateNormalizer::class);
    }


    function let()
    {
        $this->beConstructedWith();
    }

    function it_normalizes_rate_per_day(Rate $rate, Product $product, Partner $partner)
    {
        $rate->getStart()->willReturn($start = new \DateTime('2019-01-05'));
        $rate->getEnd()->willReturn($end = new \DateTime('2019-01-07'));
        $rate->getAmount()->willReturn(69.69);
        
        $partner->getCurrency()->willReturn('EUR');
        $product->getPartner()->willReturn($partner);
        $rate->getProduct()->willReturn($product);
        $this
            ->normalize($rate, [])
            ->shouldBeLike(
                [
                    [
                        'UnitMultiplier' => 1,
                        'EffectiveDate'  => '2019-01-05',
                        'ExpireDate'     => '2019-01-06',
                        'Base'           => [
                            'AmountAfterTax' => 69.69,
                            'CurrencyCode'   => 'EUR',
                        ],
                    ],
                    [
                        'UnitMultiplier' => 1,
                        'EffectiveDate'  => '2019-01-06',
                        'ExpireDate'     => '2019-01-07',
                        'Base'           => [
                            'AmountAfterTax' => 69.69,
                            'CurrencyCode'   => 'EUR',
                        ],
                    ]
                ]
            );
    }

    function it_supports_normalization_of_rate_object()
    {
        $this->supportsNormalization(Rate::class)->shouldBe(true);
        $this->supportsNormalization(\stdClass::class)->shouldBe(false);
    }

    function it_does_not_support_denormalization()
    {
        $this->supportsDenormalization(Rate::class)->shouldBe(false);
    }
}

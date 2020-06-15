<?php

namespace spec\App\Service\Iresa\Serializer;

use App\Model\Factory\ProductRateFactory;
use App\Model\Factory\RateFactory;
use App\Model\PartnerInterface;
use App\Model\ProductInterface;
use App\Model\ProductRate;
use App\Model\Rate;
use App\Service\Iresa\Serializer\ProductRateNormalizer;
use App\Service\Loader\ProductLoader;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductRateNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRateNormalizer::class);
    }

    function let(ProductLoader $productLoader, ProductRateFactory $productRateFactory, RateFactory $rateFactory)
    {
        $this->beConstructedWith($productLoader, $productRateFactory, $rateFactory);
    }

    function it_returns_null_if_room_type_code_not_present_during_denormalization()
    {
        $this->denormalize(new \stdClass())->shouldBe(null);
    }

    function it_returns_null_if_product_not_found_in_db_during_denormalization(ProductLoader $productLoader, PartnerInterface $partner)
    {
        $productLoader->find($partner, 'product_id')->willReturn();
        $this->denormalize((object) ['roomTypeCode' => 'product_id'], ['partner' => $partner])->shouldBe(null);
    }

    function it_denormalizes_to_product_rate(Rate $rate, Rate $rate1, ProductRate $productRate, PartnerInterface $partner, ProductInterface $product, ProductLoader $productLoader, ProductRateFactory $productRateFactory, RateFactory $rateFactory)
    {
        $productLoader->find($partner, 'product_id')->willReturn($product);
        $productRateFactory->create($product)->willReturn($productRate);

        $rateFactory
            ->create(
                Argument::that(function (\DateTime $start) {
                    return $start->format('Y-m-d') === '2018-01-12';
                }),
                Argument::that(function (\DateTime $end) {
                    return $end->format('Y-m-d') === '2018-01-12';
                }),
                12.2,
                $product
            )
            ->shouldBeCalled()
            ->willReturn($rate);
        $productRate->addRate($rate)->shouldBeCalled();

        $rateFactory
            ->create(
                Argument::that(function (\DateTime $start) {
                    return $start->format('Y-m-d') === '2018-02-15';
                }),
                Argument::that(function (\DateTime $end) {
                    return $end->format('Y-m-d') === '2018-02-15';
                }),
                0,
                $product
            )
            ->shouldBeCalled()
            ->willReturn($rate1);
        $productRate->addRate($rate1)->shouldBeCalled();

        $dataRate = (object) [
            'date'   => '2018-01-12T00:00:00.0000000+01:00',
            'amount' => '12.2'
        ];

        $dataRate1 = (object) [
            'date' => '2018-02-15T00:00:00.0000000+01:00'
        ];


        $this
            ->denormalize(
                (object) [
                    'roomTypeCode' => 'product_id',
                    'rates'        => [
                        $dataRate,
                        $dataRate1
                    ]
                ],
                ['partner' => $partner]
            )
            ->shouldBe($productRate);
    }

    function it_only_supports_product_rate_denormalization()
    {
        $this->supportsDenormalization(ProductRate::class)->shouldBe(true);
        $this->supportsDenormalization(\stdClass::class)->shouldBe(false);
    }

    function it_does_not_support_normalization()
    {
        $this->supportsNormalization(ProductRate::class)->shouldBe(false);
    }
}

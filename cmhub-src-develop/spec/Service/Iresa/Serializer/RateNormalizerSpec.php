<?php

namespace spec\App\Service\Iresa\Serializer;

use App\Model\Factory\RateFactory;
use App\Model\ProductInterface;
use App\Model\Rate;
use App\Service\Iresa\Serializer\RateNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RateNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RateNormalizer::class);
    }

    function let(RateFactory $rateFactory)
    {
        $this->beConstructedWith($rateFactory);
    }

    function it_denormalizes_to_rate_object(ProductInterface $product, RateFactory $rateFactory, Rate $rate)
    {
        $data = (object) [
            'dateStart' => '2018-09-09T00:00:00.0000000+01:00',
            'dateEnd'   => '2018-09-19T00:00:00.0000000+01:00',
            'amount'    => '30.52'
        ];

        $rateFactory
            ->create(
                Argument::that(function (\DateTime $start) {
                    return '2018-09-09' === $start->format('Y-m-d');
                }),
                Argument::that(function (\DateTime $end) {
                    return '2018-09-19' === $end->format('Y-m-d');
                }),
                '30.52',
                $product
            )
            ->shouldBeCalled()
            ->willReturn($rate);

        $this->denormalize($data, ['product' => $product])->shouldBe($rate);
    }

    function it_only_supports_rate_denormalization() {
        $this->supportsDenormalization(Rate::class)->shouldBe(true);
        $this->supportsDenormalization(\stdClass::class)->shouldBe(false);
    }

    function it_does_not_support_normalization() {
        $this->supportsNormalization(Rate::class)->shouldBe(false);
    }
}

<?php

namespace spec\App\Service\ChannelManager\SoapOta\Serializer\V2010A;

use App\Exception\ValidationException;
use App\Model\Factory\RateFactory;
use App\Model\ProductInterface;
use App\Model\Rate;
use App\Service\ChannelManager\SoapOta\Serializer\V2010A\RateNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

class RateNormalizerSpec extends ObjectBehavior
{
    private $rate;

    function let(RateFactory $rateFactory)
    {
        $this->beConstructedWith($rateFactory);
        $this->rate = [
            'BaseByGuestAmts' => [
                'BaseByGuestAmt' => [
                    'AmountAfterTax' => '2'
                ]
            ],
            'CurrencyCode' => 'EUR'
        ];
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RateNormalizer::class);
    }

    function it_doesnt_normalize()
    {
        $this->shouldThrow(MethodNotImplementedException::class)->during('normalize', [json_encode(["test" => "test"]), ["test" => "test"]]);
    }

    function it_doesnt_supports_normalization()
    {
        $this->supportsNormalization(Rate::class)->shouldBe(false);
    }

    function it_supports_denormalization()
    {
        $this->supportsDenormalization(Rate::class)->shouldBe(true);
    }

    function it_denormalizes_amount_incorrect()
    {
        $this->rate['BaseByGuestAmts']['BaseByGuestAmt']['AmountAfterTax'] = -1;
        $this->shouldThrow(ValidationException::class)->during('denormalize', [json_decode(json_encode($this->rate)), ["test" => "test"]]);
    }

    function it_denormalizes(
        Rate $rate,
        ProductInterface $product,
        RateFactory $rateFactory
    )
    {
        $rateFactory->create(
            Argument::that(function (\DateTime $start) {
                return $start->format('Y-m-d') === '2018-09-01';
            }),
            Argument::that(function (\DateTime $end) {
                return $end->format('Y-m-d') === '2018-09-11';
            }),
            2,
            $product
        )->willReturn($rate);

        $this->denormalize(json_decode(json_encode($this->rate)), ['product' => $product, 'startDate' => (\DateTime::createFromFormat('Y-m-d', '2018-09-01')), 'endDate' => (\DateTime::createFromFormat('Y-m-d', '2018-09-11'))])->shouldBe($rate);
    }
}

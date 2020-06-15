<?php

namespace spec\App\Service\ChannelManager\SoapOta\Serializer\V2016A;

use App\Exception\ValidationException;
use App\Model\Factory\ProductRateFactory;
use App\Model\Factory\RateFactory;
use App\Model\ProductInterface;
use App\Model\ProductRate;
use App\Model\Rate;
use App\Service\ChannelManager\SoapOta\Serializer\V2016A\ProductRateNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

class ProductRateNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRateNormalizer::class);
    }

    function let(ProductRateFactory $productRateFactory, RateFactory $rateFactory)
    {
        $this->beConstructedWith($productRateFactory, $rateFactory);
    }

    function it_supports_denormalization()
    {
        $this->supportsDenormalization(ProductRate::class)->shouldBe(true);
    }

    function it_doesnt_support_normalization()
    {
        $this->supportsNormalization(ProductRate::class)->shouldBe(false);
    }

    function it_doesnt_normalize()
    {
        $this->shouldThrow(MethodNotImplementedException::class)->during('normalize', ['hola']);
    }

    function it_denormalizes(
        ProductRateFactory $productRateFactory,
        RateFactory $rateFactory,
        ProductRate $productRate,
        Rate $rate,
        ProductInterface $product
    )
    {
        $request = [
            [
                "BaseByGuestAmts" => [
                    "BaseByGuestAmt" => [
                        "AmountAfterTax" => "1",
                        "DecimalPlaces" => "0",
                    ],
                ],
            ],
        ];

        $requestClass = json_decode(json_encode($request));

        $productRateFactory->create($product)->willReturn($productRate);


        $rateFactory
            ->create(
                Argument::that(function (\DateTime $start) {
                    return $start->format('Y-m-d') === '2019-02-21';
                }),
                Argument::that(function (\DateTime $end) {
                    return $end->format('Y-m-d') === '2019-02-28';
                }),
                1,
                $product
            )
            ->willReturn($rate);

        $productRate->addRate($rate)->willReturn($productRate);

        $this->denormalize($requestClass, ['product' => $product, 'startDate' => new \DateTime('2019-02-21'), 'endDate' => new \DateTime('2019-02-28')])->shouldBe($productRate);
    }

    function it_throws_validation_exception_due_to_amount(
        ProductRateFactory $productRateFactory,
        ProductRate $productRate,
        ProductInterface $product
    )
    {
        $request = [
            [
                "BaseByGuestAmts" => [
                    "BaseByGuestAmt" => [
                        "AmountAfterTax" => "-1",
                        "DecimalPlaces" => "0",
                    ],
                ],
            ],
        ];

        $requestClass = json_decode(json_encode($request));

        $productRateFactory->create($product)->willReturn($productRate);

        $this->shouldThrow(ValidationException::class)->during('denormalize', [$requestClass, ['product' => $product, 'startDate' => (new \DateTime('2019-02-21')), 'endDate' => (new \DateTime('2019-02-28'))]]);
    }

    function it_throws_validation_exception_due_to_decimal_places(
        ProductRateFactory $productRateFactory,
        ProductRate $productRate,
        ProductInterface $product
    )
    {
        $request = [
            [
                "BaseByGuestAmts" => [
                    "BaseByGuestAmt" => [
                        "AmountAfterTax" => "0",
                        "DecimalPlaces" => "-1",
                    ],
                ],
            ],
        ];

        $requestClass = json_decode(json_encode($request));

        $productRateFactory->create($product)->willReturn($productRate);

        $this->shouldThrow(ValidationException::class)->during('denormalize', [$requestClass, ['product' => $product, 'startDate' => (new \DateTime('2019-02-21')), 'endDate' => (new \DateTime('2019-02-28'))]]);
    }
}

<?php

namespace spec\App\Service\ChannelManager\SoapOta\Serializer\V2010A;

use App\Exception\DateFormatException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\Factory\ProductRateFactory;
use App\Model\PartnerInterface;
use App\Model\ProductInterface;
use App\Model\ProductRate;
use App\Model\Rate;
use App\Model\RatePlanCode;
use App\Service\ChannelManager\SoapOta\Serializer\V2010A\ProductRateNormalizer;
use App\Service\ChannelManager\SoapOta\Serializer\V2010A\RateNormalizer;
use App\Service\Loader\ProductLoader;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

class ProductRateNormalizerSpec extends ObjectBehavior
{
    private $rates;

    private $rateAmountMessages;

    function let(ProductLoader $productLoader, ProductRateFactory $productRateFactory, RateNormalizer $rateNormalizer)
    {
        $this->beConstructedWith($productLoader, $productRateFactory, $rateNormalizer);
        $this->rates = [
            'BaseByGuestAmts' => [
                'BaseByGuestAmt' => [
                    'AmountAfterTax' => '2'
                ]
            ],
            'CurrencyCode'    => 'EUR'
        ];

        $this->rateAmountMessages = [
            'StatusApplicationControl' => [
                'RatePlanCode' => RatePlanCode::SBX,
                'InvTypeCode'  => 'ROOMID1',
                'Start'        => '2018-09-01',
                'End'          => '2018-09-11',
            ],
            'Rates'                    => [
                'Rate' => $this->rates
            ],
        ];
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRateNormalizer::class);
    }

    function it_doesnt_normalize()
    {
        $this->shouldThrow(MethodNotImplementedException::class)->during('normalize', [
            json_encode(["test" => "test"]),
            ["test" => "test"]
        ]);
    }

    function it_doesnt_supports_normalization()
    {
        $this->supportsNormalization(ProductRate::class)->shouldBe(false);
    }

    function it_supports_denormalization()
    {
        $this->supportsDenormalization(ProductRate::class)->shouldBe(true);
    }

    function it_denormalizes_product_not_found(PartnerInterface $partner)
    {
        $this->shouldThrow(ProductNotFoundException::class)->during('denormalize', [
            json_decode(json_encode($this->rateAmountMessages)),
            ["partner" => $partner]
        ]);
    }

    function it_denormalizes_end_date_greater_than_start_date(PartnerInterface $partner, ProductLoader $productLoader, ProductInterface $product)
    {
        $productLoader->find($partner, 'ROOMID1')->willReturn($product);
        $this->rateAmountMessages['StatusApplicationControl']['Start'] = '2018-09-11';
        $this->rateAmountMessages['StatusApplicationControl']['End'] = '2018-09-01';

        $this->shouldThrow(ValidationException::class)->during('denormalize', [
            json_decode(json_encode($this->rateAmountMessages)),
            ["partner" => $partner]
        ]);
    }

    function it_denormalizes_wrong_date_format(PartnerInterface $partner, ProductLoader $productLoader, ProductInterface $product)
    {
        $productLoader->find($partner, 'ROOMID1')->willReturn($product);
        $this->rateAmountMessages['StatusApplicationControl']['Start'] = '2018-09-01';
        $this->rateAmountMessages['StatusApplicationControl']['End'] = '2018-011';

        $this->shouldThrow(DateFormatException::class)->during('denormalize', [
            json_decode(json_encode($this->rateAmountMessages)),
            ["partner" => $partner]
        ]);
    }

    function it_denormalizes(
        ProductLoader $productLoader,
        ProductInterface $product,
        PartnerInterface $partner,
        ProductRate $productRate,
        RateNormalizer $rateNormalizer,
        Rate $rate,
        ProductRateFactory $productRateFactory
    )
    {
        $productLoader->find($partner, 'ROOMID1')->willReturn($product);

        $productRateFactory->create($product)->willReturn($productRate);

        $rateNormalizer
            ->denormalize(
                json_decode(json_encode($this->rates)),
                Argument::that(
                    function (array $options) use ($product) {
                        if ($options['product'] !== $product->getWrappedObject()) {
                            return false;
                        }

                        if ($options['startDate']->format('Y-m-d') !== '2018-09-01') {
                            return false;
                        }

                        if ($options['endDate']->format('Y-m-d') !== '2018-09-11') {
                            return false;
                        }

                        return true;
                    }
                )
            )
            ->shouldBeCalled()
            ->willReturn($rate);

        $productRate->addRate($rate)->willReturn($productRate);

        $this->denormalize(json_decode(json_encode($this->rateAmountMessages)), ['partner' => $partner])->shouldBe($productRate);
    }
}

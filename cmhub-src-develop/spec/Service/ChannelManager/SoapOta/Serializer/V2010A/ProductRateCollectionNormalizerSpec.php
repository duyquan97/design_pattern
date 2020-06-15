<?php

namespace spec\App\Service\ChannelManager\SoapOta\Serializer\V2010A;

use App\Model\Factory\ProductRateCollectionFactory;
use App\Model\PartnerInterface;
use App\Model\ProductRateCollection;
use App\Model\ProductRateInterface;
use App\Model\RatePlanCode;
use App\Service\ChannelManager\SoapOta\Serializer\V2010A\ProductRateCollectionNormalizer;
use App\Service\ChannelManager\SoapOta\Serializer\V2010A\ProductRateNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

class ProductRateCollectionNormalizerSpec extends ObjectBehavior
{
    private $rateAmountMessages;

    function let(ProductRateCollectionFactory $productRateCollectionFactory, ProductRateNormalizer $productRateNormalizer)
    {
        $this->beConstructedWith($productRateCollectionFactory, $productRateNormalizer);
        $this->rateAmountMessages = [
            'StatusApplicationControl' => [
                'RatePlanCode' => RatePlanCode::SBX,
                'InvTypeCode' => 'ROOMID1',
                'Start' => '2018-09-01',
                'End' => '2018-09-11',
            ],
            'Rates' => [
                'Rate' => [
                    'BaseByGuestAmts' => [
                        'BaseByGuestAmt' => [
                            'AmountAfterTax' => '2'
                        ]
                    ],
                    'CurrencyCode' => 'EUR'
                ]
            ],
        ];
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRateCollectionNormalizer::class);
    }

    function it_doesnt_normalize()
    {
        $this->shouldThrow(MethodNotImplementedException::class)->during('normalize', [json_encode(["test" => "test"]), ["test" => "test"]]);
    }

    function it_doesnt_supports_normalization()
    {
        $this->supportsNormalization(ProductRateCollection::class)->shouldBe(false);
    }

    function it_supports_denormalization()
    {
        $this->supportsDenormalization(ProductRateCollection::class)->shouldBe(true);
    }

    function it_denormalizes(
        PartnerInterface $partner,
        ProductRateCollection $productRateCollection,
        ProductRateCollectionFactory $productRateCollectionFactory,
        ProductRateNormalizer $productRateNormalizer,
        ProductRateInterface $productRate
    )
    {
        $productRateCollectionFactory->create($partner)->willReturn($productRateCollection);
        $productRateNormalizer->denormalize(json_decode(json_encode($this->rateAmountMessages)), ['partner' => $partner])->willReturn($productRate);
        $productRateCollection->addProductRate($productRate)->willReturn($productRateCollection);

        $this->denormalize(json_decode(json_encode([$this->rateAmountMessages])), ['partner' => $partner])->shouldBe($productRateCollection);
    }
}

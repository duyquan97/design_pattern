<?php

namespace spec\App\Service\ChannelManager\SoapOta\Serializer\V2016A;

use App\Exception\DateFormatException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\Factory\ProductRateCollectionFactory;
use App\Model\PartnerInterface;
use App\Model\ProductInterface;
use App\Model\ProductRateCollection;
use App\Model\ProductRateInterface;
use App\Service\ChannelManager\SoapOta\Serializer\V2016A\ProductRateCollectionNormalizer;
use App\Service\ChannelManager\SoapOta\Serializer\V2016A\ProductRateNormalizer;
use App\Service\Loader\ProductLoader;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

class ProductRateCollectionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRateCollectionNormalizer::class);
    }

    function let(ProductRateCollectionFactory $productRateCollectionFactory, ProductLoader $productLoader, ProductRateNormalizer $productRateNormalizer)
    {
        $this->beConstructedWith($productRateCollectionFactory, $productLoader, $productRateNormalizer);
    }

    function it_supports_denormalization()
    {
        $this->supportsDenormalization(ProductRateCollection::class)->shouldBe(true);
    }

    function it_doesnt_support_normalization()
    {
        $this->supportsNormalization(ProductRateCollection::class)->shouldBe(false);
    }

    function it_doesnt_normalize()
    {
        $this->shouldThrow(MethodNotImplementedException::class)->during('normalize', ['hola']);
    }

    function it_denormalizes(
        ProductRateCollection $productRateCollection,
        ProductRateCollectionFactory $productRateCollectionFactory,
        PartnerInterface $partner,
        ProductLoader $productLoader,
        ProductInterface $product,
        ProductRateNormalizer $productRateNormalizer,
        ProductRateInterface $productRate
    )
    {
        $request2 = [
            "BaseByGuestAmts" => [
                "BaseByGuestAmt" => [
                    [
                        "AmountAfterTax" => "1",
                    ],
                ],
            ],
        ];

        $request2Class = json_decode(json_encode($request2));

        $request = [
            [
                "StatusApplicationControl" =>
                    [
                        "InvTypeCode" => "358999",
                        "Start" => "2019-02-21",
                        "End" => "2019-02-28",
                        "RatePlanCode" => "1",
                    ],
                "Rates" => [
                    "Rate" => $request2,
                ],
            ],
        ];

        $requestClass = json_decode(json_encode($request));

        $productRateCollectionFactory->create($partner)->willReturn($productRateCollection);
        $productLoader->find($partner, '358999')->willReturn($product);
        $productRateCollection->addEnabledWeekDays(Argument::any())->shouldBeCalledOnce();
        $productRateCollection->setEnabledWeekDays([])->shouldBeCalledOnce();

        $productRateNormalizer
            ->denormalize(
                [$request2Class],
                Argument::that(function (array $data) use ($product) {
                    if($data['product'] !== $product->getWrappedObject()) {
                        return false;
                    }

                    $startDate = $data['startDate'];
                    if($startDate->format('Y-m-d') !== '2019-02-21') {
                        return false;
                    }

                    $endDate = $data['endDate'];
                    if($endDate->format('Y-m-d') !== '2019-02-28') {
                        return false;
                    }

                    return true;
                }))
                ->willReturn($productRate);

        $productRateCollection->addProductRate($productRate)->willReturn($productRateCollection);

        $this->denormalize($requestClass, ['partner' => $partner])->shouldBe($productRateCollection);
    }

    function it_throws_product_not_found_exception(
        ProductRateCollection $productRateCollection,
        ProductRateCollectionFactory $productRateCollectionFactory,
        PartnerInterface $partner,
        ProductLoader $productLoader
    )
    {
        $request2 = [
            "BaseByGuestAmts" => [
                "BaseByGuestAmt" => [
                    [
                        "AmountAfterTax" => "1",
                    ],
                ],
            ],
        ];

        $request = [
            [
                "StatusApplicationControl" =>
                    [
                        "InvTypeCode" => "358999",
                        "Start" => "2019-02-21",
                        "End" => "2019-02-28",
                        "RatePlanCode" => "1",
                    ],
                "Rates" => [
                    "Rate" => $request2,
                ],
            ],
        ];

        $requestClass = json_decode(json_encode($request));

        $productRateCollectionFactory->create($partner)->willReturn($productRateCollection);
        $productLoader->find($partner, '358999')->willReturn(null);

        $this->shouldThrow(ProductNotFoundException::class)->during('denormalize', [$requestClass, ['partner' => $partner]]);
    }

    function it_throws_validation_exception(
        ProductRateCollection $productRateCollection,
        ProductRateCollectionFactory $productRateCollectionFactory,
        PartnerInterface $partner,
        ProductLoader $productLoader,
        ProductInterface $product
    )
    {
        $request2 = [
            "BaseByGuestAmts" => [
                "BaseByGuestAmt" => [
                    [
                        "AmountAfterTax" => "1",
                    ],
                ],
            ],
        ];

        $request = [
            [
                "StatusApplicationControl" =>
                    [
                        "InvTypeCode" => "358999",
                        "Start" => "2019-02-28",
                        "End" => "2019-02-21",
                        "RatePlanCode" => "1",
                    ],
                "Rates" => [
                    "Rate" => $request2,
                ],
            ],
        ];

        $requestClass = json_decode(json_encode($request));

        $productRateCollectionFactory->create($partner)->willReturn($productRateCollection);
        $productLoader->find($partner, '358999')->willReturn($product);

        $this->shouldThrow(ValidationException::class)->during('denormalize', [$requestClass, ['partner' => $partner]]);
    }

    function it_throws_date_format_exception(
        ProductRateCollection $productRateCollection,
        ProductRateCollectionFactory $productRateCollectionFactory,
        PartnerInterface $partner,
        ProductLoader $productLoader,
        ProductInterface $product
    )
    {
        $request2 = [
            "BaseByGuestAmts" => [
                "BaseByGuestAmt" => [
                    [
                        "AmountAfterTax" => "1",
                    ],
                ],
            ],
        ];

        $request = [
            [
                "StatusApplicationControl" =>
                    [
                        "InvTypeCode" => "358999",
                        "Start" => "20190228",
                        "End" => "20190221",
                        "RatePlanCode" => "1",
                    ],
                "Rates" => [
                    "Rate" => $request2,
                ],
            ],
        ];

        $requestClass = json_decode(json_encode($request));

        $productRateCollectionFactory->create($partner)->willReturn($productRateCollection);
        $productLoader->find($partner, '358999')->willReturn($product);

        $this->shouldThrow(DateFormatException::class)->during('denormalize', [$requestClass, ['partner' => $partner]]);
    }
}

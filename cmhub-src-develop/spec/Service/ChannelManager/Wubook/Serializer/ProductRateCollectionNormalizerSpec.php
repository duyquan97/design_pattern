<?php

namespace spec\App\Service\ChannelManager\Wubook\Serializer;

use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\Factory\ProductRateCollectionFactory;
use App\Model\PartnerInterface;
use App\Model\ProductInterface;
use App\Model\ProductRate;
use App\Model\ProductRateCollection;
use App\Model\RateInterface;
use App\Model\RatePlanCode;
use App\Service\ChannelManager\Wubook\Serializer\ProductRateCollectionNormalizer;
use App\Service\ChannelManager\Wubook\Serializer\RateNormalizer;
use App\Service\Loader\ProductLoader;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

class ProductRateCollectionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRateCollectionNormalizer::class);
    }

    function let(ProductRateCollectionFactory $productRateCollectionFactory, RateNormalizer $rateNormalizer, ProductLoader $productLoader)
    {
        $this->beConstructedWith($productRateCollectionFactory, $rateNormalizer, $productLoader);
    }

    function it_doesnt_normalize(PartnerInterface $partner)
    {
        $request = [
            "dfrom" => "2019-01-12",
            "dto" => "2019-01-24",
            "price" => "1",
            "rate_id" => RatePlanCode::SBX,
        ];

        $this->shouldThrow(MethodNotImplementedException::class)->during('normalize', [json_encode($request), ['partner' => $partner]]);
    }

    function it_denormalizes_valid_data(
        ProductRateCollection $productRateCollection,
        PartnerInterface $partner,
        ProductRateCollectionFactory $productRateCollectionFactory,
        ProductInterface $product,
        RateNormalizer $rateNormalizer,
        RateInterface $rate,
        ProductLoader $productLoader)
    {
        $requestRate = [
            [
                "dfrom" => "2019-01-01",
                "dto" => "2019-01-12",
                "room_id" => "366455",
                "price" => 90.15,
                "rate_id" => RatePlanCode::SBX,
            ],
        ];

        $request = [
            "prices" => $requestRate,
        ];

        $product->getIdentifier()->willReturn('366455');
        $productRateCollectionFactory->create($partner)->shouldBeCalled()->willReturn($productRateCollection);

        $productLoader->find($partner, '366455')->shouldBeCalled()->willReturn($product);
        $rateNormalizer->denormalize(json_decode(json_encode($requestRate[0])), ['product' => $product])->shouldBeCalled()->willReturn($rate);
        $productRateCollection->addRate($product, $rate)->shouldBeCalled()->willReturn($productRateCollection);

        $this->denormalize(json_decode(json_encode($request)), ['partner' => $partner])->shouldBe($productRateCollection);
    }

    function it_doesnt_denormalize_incorrect_room_id(PartnerInterface $partner, ProductRateCollectionFactory $productRateCollectionFactory, ProductRateCollection $productRateCollection)
    {
        $requestRate = [
            [
                "dfrom" => "2019-01-01",
                "dto" => "2019-01-12",
                "price" => 90.15,
                "rate_id" => RatePlanCode::SBX,
            ],
        ];

        $request = [
            "prices" => $requestRate,
        ];

        $productRateCollectionFactory->create($partner)->shouldBeCalled()->willReturn($productRateCollection);

        $this->shouldThrow(ValidationException::class)->during('denormalize', [json_decode(json_encode($request)), ['partner' => $partner]]);
    }

    function it_doesnt_denormalize_room_is_not_in_cmhub(PartnerInterface $partner, ProductRateCollectionFactory $productRateCollectionFactory, ProductRateCollection $productRateCollection)
    {
        $requestRate = [
            [
                "dfrom" => "2019-01-01",
                "dto" => "2019-01-12",
                "room_id" => "1",
                "price" => 90.15,
                "rate_id" => RatePlanCode::SBX,
            ],
        ];

        $request = [
            "prices" => $requestRate,
        ];

        $productRateCollectionFactory->create($partner)->shouldBeCalled()->willReturn($productRateCollection);

        $this->shouldThrow(ProductNotFoundException::class)->during('denormalize', [json_decode(json_encode($request)), ['partner' => $partner]]);
    }

    function it_doesnt_support_normalization_for()
    {
        $this->supportsNormalization(ProductRateCollection::class)->shouldBe(false);
    }

    function it_supports_denormalization_for()
    {
        $this->supportsDenormalization(ProductRateCollection::class)->shouldBe(true);
    }

    function it_doesnt_support_denormalization_for()
    {
        $this->supportsDenormalization(ProductRate::class)->shouldBe(false);
    }
}

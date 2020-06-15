<?php

namespace spec\App\Service\Iresa\Serializer;

use App\Model\Factory\ProductRateCollectionFactory;
use App\Model\PartnerInterface;
use App\Model\ProductRate;
use App\Model\ProductRateCollection;
use App\Service\Iresa\Serializer\ProductRateCollectionNormalizer;
use App\Service\Iresa\Serializer\ProductRateNormalizer;
use PhpSpec\ObjectBehavior;

class ProductRateCollectionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRateCollectionNormalizer::class);
    }

    function let(ProductRateCollectionFactory $productRateCollectionFactory, ProductRateNormalizer $productRateNormalizer)
    {
        $this->beConstructedWith($productRateCollectionFactory, $productRateNormalizer);
    }

    function it_denormalizes_to_product_rate_collection(ProductRateCollection $productRateCollection, PartnerInterface $partner, ProductRateCollectionFactory $productRateCollectionFactory, ProductRateNormalizer $productRateNormalizer, ProductRate $productRate, ProductRate $productRate1)
    {
        $context = ['partner' => $partner];
        $productRateCollectionFactory->create($partner)->willReturn($productRateCollection);

        $productRateNormalizer->denormalize($dataRate = (object) ['the' => 'productRate'], $context)->shouldBeCalled()->willReturn($productRate);
        $productRateCollection->addProductRate($productRate)->shouldBeCalled()->willReturn($productRateCollection);
        $productRateNormalizer->denormalize($dataRate1 = (object) ['the' => 'productRate1'], $context)->shouldBeCalled()->willReturn($productRate1);
        $productRateCollection->addProductRate($productRate1)->shouldBeCalled()->willReturn($productRateCollection);

        $productRateNormalizer->denormalize($dataRateNullCase = (object) ['the' => 'productRate_null_case'], $context)->shouldBeCalled()->willReturn();

        $this
            ->denormalize(
                [
                    $dataRate,
                    $dataRate1,
                    $dataRateNullCase
                ],
                $context
            )
            ->shouldBe($productRateCollection);
    }

    function it_only_supports_rate_denormalization()
    {
        $this->supportsDenormalization(ProductRateCollection::class)->shouldBe(true);
        $this->supportsDenormalization(\stdClass::class)->shouldBe(false);
    }

    function it_does_not_support_normalization()
    {
        $this->supportsNormalization(ProductRateCollection::class)->shouldBe(false);
    }
}

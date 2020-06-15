<?php

namespace spec\App\Service\EAI\Serializer;

use App\Model\PartnerInterface;
use App\Model\ProductAvailability;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Model\ProductInterface;
use App\Service\EAI\Serializer\ProductAvailabilityCollectionNormalizer;
use App\Service\EAI\Serializer\ProductAvailabilityNormalizer;
use PhpSpec\ObjectBehavior;

class ProductAvailabilityCollectionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductAvailabilityCollectionNormalizer::class);
    }

    function let(ProductAvailabilityNormalizer $productAvailabilityNormalizer)
    {
        $this->beConstructedWith($productAvailabilityNormalizer);
    }

    function it_normalizes_to_iresa_request(ProductInterface $product, ProductInterface $product1, ProductAvailabilityCollectionInterface $productAvailabilityCollection, PartnerInterface $partner, ProductAvailability $productAvailability, ProductAvailability $productAvailability1, ProductAvailabilityNormalizer $productAvailabilityNormalizer)
    {
        $productAvailabilityCollection->getPartner()->willReturn($partner);
        $partner->getIdentifier()->willReturn('partner_id');
        $productAvailabilityCollection
            ->getProductAvailabilities()
            ->willReturn(
                [
                    $productAvailability,
                    $productAvailability1
                ]
            );

        $productAvailabilityNormalizer->normalize($productAvailability)->shouldBeCalled()->willReturn(['normalized_data']);
        $productAvailability->getProduct()->willReturn($product);
        $product->getIdentifier()->willReturn('product_id_1');

        $productAvailabilityNormalizer->normalize($productAvailability1)->shouldBeCalled()->willReturn(['normalized_data_1']);
        $productAvailability1->getProduct()->willReturn($product1);
        $product1->getIdentifier()->willReturn('product_id_2');

        $this
            ->normalize($productAvailabilityCollection)
            ->shouldBe(
                [
                    'normalized_data',
                    'normalized_data_1',
                ]
            );
    }

    function it_supports_denormalization_product_availability_collection()
    {
        $this->supportsDenormalization(ProductAvailabilityCollection::class)->shouldBe(true);
        $this->supportsDenormalization(\stdClass::class)->shouldBe(false);
    }


    function it_supports_normalization()
    {
        $this->supportsNormalization(ProductAvailabilityCollection::class)->shouldBe(true);
        $this->supportsNormalization(\stdClass::class)->shouldBe(false);
    }
}

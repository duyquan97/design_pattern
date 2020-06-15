<?php

namespace spec\App\Service\Iresa\Serializer;

use App\Model\Factory\ProductAvailabilityCollectionFactory;
use App\Model\PartnerInterface;
use App\Model\ProductAvailability;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Model\ProductInterface;
use App\Service\Iresa\Serializer\ProductAvailabilityCollectionNormalizer;
use App\Service\Iresa\Serializer\ProductAvailabilityNormalizer;
use PhpSpec\ObjectBehavior;

class ProductAvailabilityCollectionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductAvailabilityCollectionNormalizer::class);
    }

    function let(ProductAvailabilityNormalizer $productAvailabilityNormalizer, ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory)
    {
        $this->beConstructedWith($productAvailabilityNormalizer, $productAvailabilityCollectionFactory);
    }

    function it_denormalizes_to_product_availability_collection(ProductAvailability $productAvailability, ProductAvailability $productAvailability1, ProductAvailabilityCollection $productAvailabilityCollection, PartnerInterface $partner, ProductAvailabilityNormalizer $productAvailabilityNormalizer, ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory)
    {
        $availabilityOne = (object) ['availability' => 'first'];
        $availabilityTwo = (object) ['availability' => 'second'];
        $productAvailabilityCollectionFactory->create($partner)->willReturn($productAvailabilityCollection);

        $productAvailabilityNormalizer->denormalize($availabilityOne, ['partner' => $partner])->willReturn($productAvailability);
        $productAvailabilityCollection->addProductAvailability($productAvailability)->shouldBeCalled();
        $productAvailabilityNormalizer->denormalize($availabilityTwo, ['partner' => $partner])->willReturn($productAvailability1);
        $productAvailabilityCollection->addProductAvailability($productAvailability1)->shouldBeCalled();

        $this
            ->denormalize(
                (object) [
                    $availabilityOne,
                    $availabilityTwo
                ],
                ['partner' => $partner]
            )
            ->shouldBe($productAvailabilityCollection);
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

        $productAvailabilityNormalizer->normalize($productAvailability)->shouldBeCalled()->willReturn(['the' => 'normalized_data']);
        $productAvailability->getProduct()->willReturn($product);
        $product->getIdentifier()->willReturn('product_id_1');

        $productAvailabilityNormalizer->normalize($productAvailability1)->shouldBeCalled()->willReturn(['the1' => 'normalized_data']);
        $productAvailability1->getProduct()->willReturn($product1);
        $product1->getIdentifier()->willReturn('product_id_2');

        $this
            ->normalize($productAvailabilityCollection)
            ->shouldBe(
                [
                    'partnerCode'    => 'partner_id',
                    'availabilities' => [
                        [
                            'roomTypeCode' => 'product_id_1',
                            'roomTypes'    => ['the' => 'normalized_data'],
                        ],
                        [
                            'roomTypeCode' => 'product_id_2',
                            'roomTypes'    => ['the1' => 'normalized_data']
                        ]
                    ]
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

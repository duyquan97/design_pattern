<?php

namespace spec\App\Service\ChannelManager\Wubook\Serializer;

use App\Model\AvailabilityInterface;
use App\Model\Factory\ProductAvailabilityCollectionFactory;
use App\Model\PartnerInterface;
use App\Model\ProductAvailability;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductInterface;
use App\Service\ChannelManager\Wubook\Serializer\AvailabilityNormalizer;
use App\Service\ChannelManager\Wubook\Serializer\ProductAvailabilityCollectionNormalizer;
use App\Service\ChannelManager\Wubook\Serializer\RestrictionNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

class ProductAvailabilityCollectionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductAvailabilityCollectionNormalizer::class);
    }

    function let(ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory, AvailabilityNormalizer $availabilityNormalizer, RestrictionNormalizer $restrictionNormalizer)
    {
        $this->beConstructedWith($productAvailabilityCollectionFactory, $availabilityNormalizer, $restrictionNormalizer);
    }

    function it_doesnt_normalize(ProductInterface $product)
    {
        $request = [
            "dfrom" => "2019-01-12",
            "dto" => "2019-01-24",
            "avail" => "1"
        ];

        $this->shouldThrow(MethodNotImplementedException::class)->during('normalize', [json_encode($request), ['product' => $product]]);
    }

    function it_denormalizes_valid_data_without_restriction(ProductAvailabilityCollection $productAvailabilityCollection, PartnerInterface $partner, ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory, ProductInterface $product, AvailabilityNormalizer $availabilityNormalizer, AvailabilityInterface $availability)
    {
        $requestAvailability = [[
            "dfrom" => "2019-01-01",
            "dto" => "2019-01-12",
            "room_id" => "366455",
            "avail" => "1",
        ],];

        $request = [
            "availability" => $requestAvailability,
        ];

        $product->getIdentifier()->willReturn('366455');
        $productAvailabilityCollectionFactory->create($partner)->shouldBeCalled()->willReturn($productAvailabilityCollection);

        $availabilityNormalizer->denormalize(json_decode(json_encode($requestAvailability[0])), ['partner' => $partner])->shouldBeCalled()->willReturn($availability);
        $productAvailabilityCollection->addAvailability($availability)->shouldBeCalled()->willReturn($productAvailabilityCollection);

        $this->denormalize(json_decode(json_encode($request)), ['partner' => $partner])->shouldBe($productAvailabilityCollection);
    }

    function it_denormalizes_valid_data_with_restriction
    (
        ProductAvailabilityCollection $productAvailabilityCollection,
        PartnerInterface $partner,
        ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory,
        AvailabilityNormalizer $availabilityNormalizer,
        AvailabilityInterface $availability,
        AvailabilityInterface $availability1,
        RestrictionNormalizer $restrictionNormalizer
    )
    {
        $requestAvailability = [[
            "dfrom" => "2019-01-01",
            "dto" => "2019-01-12",
            "room_id" => "366455",
            "avail" => "1",
        ],];
        $requestRestrictions = [[
            "dfrom" => "2019-01-01",
            "dto" => "2019-01-02",
            "room_id" => "366455",
            "rate_id" => "1",
            "closed" => true,
            "cta" => true,
            "ctd" => false,
            "minstay" => 1,
            "maxstay" => 0,
            "minstayarr" => 1
        ],];

        $request = [
            "availability" => $requestAvailability,
            "restrictions" => $requestRestrictions,
        ];

        $productAvailabilityCollectionFactory->create($partner)->shouldBeCalled()->willReturn($productAvailabilityCollection);

        $availabilityNormalizer->denormalize(json_decode(json_encode($requestAvailability[0])), ['partner' => $partner])->shouldBeCalled()->willReturn($availability);

        $restrictionNormalizer->denormalize(json_decode(json_encode($requestRestrictions[0])), ['partner' => $partner, 'collection' => $productAvailabilityCollection])->shouldBeCalled()->willReturn($productAvailabilityCollection);

        $productAvailabilityCollection->addAvailability($availability)->shouldBeCalled()->willReturn($productAvailabilityCollection);

        $this->denormalize(json_decode(json_encode($request)), ['partner' => $partner])->shouldBe($productAvailabilityCollection);
    }

    function it_doesnt_support_normalization_for()
    {
        $this->supportsNormalization(ProductAvailabilityCollection::class)->shouldBe(false);
    }

    function it_supports_denormalization_for()
    {
        $this->supportsDenormalization(ProductAvailabilityCollection::class)->shouldBe(true);
    }

    function it_doesnt_support_denormalization_for()
    {
        $this->supportsDenormalization(ProductAvailability::class)->shouldBe(false);
    }
}

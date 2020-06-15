<?php

namespace spec\App\Service\ChannelManager\BB8\Serializer;

use App\Entity\Availability;
use App\Exception\PartnerNotFoundException;
use App\Exception\ProductNotFoundException;
use App\Model\Availability as AvailabilityModel;
use App\Entity\Partner;
use App\Entity\Product;
use App\Model\Factory\AvailabilityFactory;
use App\Model\Factory\ProductAvailabilityCollectionFactory;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Model\ProductAvailabilityInterface;
use App\Model\RatePlanCode;
use App\Service\ChannelManager\BB8\Serializer\AvailabilityCollectionNormalizer;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Argument;

class AvailabilityCollectionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AvailabilityCollectionNormalizer::class);
    }

    function let(
        PartnerLoader $partnerLoader,
        ProductLoader $productLoader,
        EntityManagerInterface $entityManager,
        ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory,
        AvailabilityFactory $availabilityFactory
    ) {
        $this->beConstructedWith($partnerLoader, $productLoader, $entityManager, $productAvailabilityCollectionFactory, $availabilityFactory);
    }

    function it_normalize(
        ProductAvailabilityCollectionInterface $productAvailabilityCollection,
        ProductAvailabilityInterface $productAvailability,
        Availability $availability,
        Product $product,
        Partner $partner
    ) {
        $createdAt = \DateTime::createFromFormat('Y-m-d', '2018-12-21');
        $udpdatedAt = \DateTime::createFromFormat('Y-m-d', '2018-12-21');

        $availability->getStart()->shouldBeCalled()->willReturn(\DateTime::createFromFormat('Y-m-d', '2018-12-21'));
        $availability->getUpdatedAt()->willReturn($createdAt);
        $availability->getCreatedAt()->willReturn($udpdatedAt);
        $availability->getProduct()->willReturn($product);
        $availability->getStock()->willReturn(2);
        $availability->isStopSale()->willReturn(false);

        $partner->getIdentifier()->willReturn('partner');
        $product->getPartner()->willReturn($partner);

        $product->getIdentifier()->willReturn('product');
        $productAvailability->getAvailabilities()->willReturn([$availability]);

        $this->normalize([$productAvailability])->shouldBe(
            [
                [
                    'date' => '2018-12-21',
                    'quantity' => 2,
                    'externalRateBandId' => RatePlanCode::SBX,
                    'externalPartnerId' => 'partner',
                    'externalRoomId' => 'product',
                    'type' => AvailabilityCollectionNormalizer::BB8_TYPE_INSTANT,
                    'externalCreatedAt' => $createdAt->format('Y-m-d\TH:i:sP'),
                    'externalUpdatedAt' => $createdAt->format('Y-m-d\TH:i:sP')
                ]
            ]
        );
    }

    /**
     * @param AvailabilityFactory|Collaborator $availabilityFactory
     * @param AvailabilityModel|Collaborator $availability
     * @param Product|Collaborator $product
     * @param ProductAvailabilityCollection|Collaborator $availabilityCollection
     * @param PartnerLoader $partnerLoader
     * @param Partner $partner
     * @param ProductLoader $productLoader
     * @param ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory
     * @throws PartnerNotFoundException
     * @throws ProductNotFoundException
     */
    function it_denormalize_instant(
        AvailabilityFactory $availabilityFactory,
        AvailabilityModel $availability,
        Product $product,
        ProductAvailabilityCollection $availabilityCollection,
        PartnerLoader $partnerLoader,
        Partner $partner,
        ProductLoader $productLoader,
        ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory
    ) {
        $testData = '[
          {
            "date": "2019-03-20",
            "quantity": 1,
            "type": "instant",
            "externalRateBandId": "SBX",
            "externalPartnerId": "00019158",
            "externalRoomId": "110224",
            "externalCreatedAt": "2019-03-20T12:22:34.392Z",
            "externalUpdatedAt": "2019-03-20T12:22:34.392Z"
          }
        ]';

        $partnerLoader->find('00019158')->willReturn($partner);
        $partner->getProducts()->willReturn([$product]);
        $product->getIdentifier()->willReturn('110224');
        
        $productLoader->find($partner, '110224')->willReturn($product);

        $availabilityFactory
            ->create(
                Argument::that(
                    function (\DateTime $start) {
                        return $start->format('Y-m-d') === '2019-03-20';
                    }
                ),
                Argument::that(
                    function (\DateTime $end) {
                        return $end->format('Y-m-d') === '2019-03-20';
                    }
                ),
                1,
                $product,
                false
            )
            ->willReturn($availability)
        ;

        $productAvailabilityCollectionFactory->create($partner)->willReturn($availabilityCollection);
        $availabilityCollection->addAvailability($availability)->shouldBeCalled();

        $this->denormalize(json_decode($testData))->shouldBe($availabilityCollection);
    }

    function it_denormalize_unavailable(
        AvailabilityFactory $availabilityFactory,
        AvailabilityModel $availability,
        Product $product,
        ProductAvailabilityCollection $availabilityCollection,
        PartnerLoader $partnerLoader,
        Partner $partner,
        ProductLoader $productLoader,
        ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory
    ) {
        $testData = '[
          {
            "date": "2019-03-20",
            "quantity": 1,
            "type": "unavailable",
            "externalRateBandId": "SBX",
            "externalPartnerId": "00019158",
            "externalRoomId": "110224",
            "externalCreatedAt": "2019-03-20T12:22:34.392Z",
            "externalUpdatedAt": "2019-03-20T12:22:34.392Z"
          }
        ]';

        $partnerLoader->find('00019158')->willReturn($partner);
        $partner->getProducts()->willReturn([$product]);
        $product->getIdentifier()->willReturn('110224');

        $productLoader->find($partner, '110224')->willReturn($product);

        $availabilityFactory
            ->create(
                Argument::that(
                    function (\DateTime $start) {
                        return $start->format('Y-m-d') === '2019-03-20';
                    }
                ),
                Argument::that(
                    function (\DateTime $end) {
                        return $end->format('Y-m-d') === '2019-03-20';
                    }
                ),
                1,
                $product,
                true
            )
            ->willReturn($availability)
        ;

        $productAvailabilityCollectionFactory->create($partner)->willReturn($availabilityCollection);
        $availabilityCollection->addAvailability($availability)->shouldBeCalled();

        $this->denormalize(json_decode($testData))->shouldBe($availabilityCollection);
    }

    function it_support_normalization()
    {
        $this->supportsNormalization(ProductAvailabilityCollection::class)->shouldBe(true);
    }

    function it_support_denormalization()
    {
        $this->supportsDenormalization(ProductAvailabilityCollection::class)->shouldBe(true);
    }
}

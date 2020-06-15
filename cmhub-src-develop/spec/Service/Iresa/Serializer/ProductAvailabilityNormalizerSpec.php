<?php

namespace spec\App\Service\Iresa\Serializer;

use App\Entity\Product;
use App\Repository\AvailabilityRepository;
use App\Model\Availability;
use App\Model\Factory\ProductAvailabilityFactory;
use App\Model\PartnerInterface;
use App\Model\ProductAvailability;
use App\Model\ProductInterface;
use App\Service\Iresa\Serializer\AvailabilityNormalizer;
use App\Service\Iresa\Serializer\ProductAvailabilityNormalizer;
use App\Service\Loader\ProductLoader;
use PhpSpec\ObjectBehavior;

class ProductAvailabilityNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductAvailabilityNormalizer::class);
    }

    function let(ProductLoader $productLoader, ProductAvailabilityFactory $productAvailabilityFactory, AvailabilityRepository $availabilityRepository, AvailabilityNormalizer $availabilityNormalizer)
    {
        $this->beConstructedWith($productLoader, $productAvailabilityFactory, $availabilityRepository, $availabilityNormalizer);
    }

    function it_normalizes_from_product_availability(
        ProductAvailability $productAvailability,
        Availability $availability,
        Availability $availability1,
        \DateTime $start,
        \DateTime $end,
        \DateTime $start1,
        \DateTime $end1
    )
    {
        $productAvailability->getAvailabilities()->willReturn([
            $availability,
            $availability1
        ]);
        $availability->getStart()->willReturn($start);
        $availability->getEnd()->willReturn($end);
        $availability->getStock()->willReturn(2);
        $availability->isStopSale()->willReturn(false);
        $start->format('Y-m-d')->willReturn('formatted_date_start');
        $end->modify('+1 day')->shouldBeCalled()->willReturn($end);
        $end->format('Y-m-d')->willReturn('formatted_date_end');


        $availability1->getStart()->willReturn($start1);
        $availability1->getEnd()->willReturn($end1);
        $availability1->getStock()->willReturn(5);
        $availability1->isStopSale()->willReturn(false);
        $start1->format('Y-m-d')->willReturn('formatted_date_2_start');
        $end1->modify('+1 day')->shouldBeCalled()->willReturn($end1);
        $end1->format('Y-m-d')->willReturn('formatted_date_2_end');

        $this
            ->normalize($productAvailability)
            ->shouldBe(
                [
                    [
                        'dateStart' => 'formatted_date_start',
                        'dateEnd'   => 'formatted_date_end',
                        'stock'     => 2
                    ],
                    [
                        'dateStart' => 'formatted_date_2_start',
                        'dateEnd'   => 'formatted_date_2_end',
                        'stock'     => 5
                    ],
                ]
            );
    }

    function it_normalizes_from_product_zero_availability(
        ProductAvailability $productAvailability,
        Availability $availability,
        Availability $availability1,
        \DateTime $start,
        \DateTime $end,
        \DateTime $start1,
        \DateTime $end1
    )
    {
        $productAvailability->getAvailabilities()->willReturn([
            $availability,
            $availability1
        ]);
        $availability->getStart()->willReturn($start);
        $availability->getEnd()->willReturn($end);
        $availability->getStock()->willReturn(0);
        $availability->isStopSale()->willReturn(false);
        $start->format('Y-m-d')->willReturn('formatted_date_start');
        $end->modify('+1 day')->shouldBeCalled()->willReturn($end);
        $end->format('Y-m-d')->willReturn('formatted_date_end');


        $availability1->getStart()->willReturn($start1);
        $availability1->getEnd()->willReturn($end1);
        $availability1->getStock()->willReturn(0);
        $availability1->isStopSale()->willReturn(false);
        $start1->format('Y-m-d')->willReturn('formatted_date_2_start');
        $end1->modify('+1 day')->shouldBeCalled()->willReturn($end1);
        $end1->format('Y-m-d')->willReturn('formatted_date_2_end');

        $this
            ->normalize($productAvailability)
            ->shouldBe(
                [
                    [
                        'dateStart' => 'formatted_date_start',
                        'dateEnd'   => 'formatted_date_end',
                        'stock'     => 0
                    ],
                    [
                        'dateStart' => 'formatted_date_2_start',
                        'dateEnd'   => 'formatted_date_2_end',
                        'stock'     => 0
                    ],
                ]
            );
    }

    function it_normalizes_from_product_null_availability(
        ProductAvailability $productAvailability,
        AvailabilityRepository $availabilityRepository,
        Availability $availability,
        Availability $availability1,
        Availability $exist,
        Product $product,
        \DateTime $start,
        \DateTime $end,
        \DateTime $start1,
        \DateTime $end1
    )
    {
        $productAvailability->getAvailabilities()->willReturn([
            $availability,
            $availability1
        ]);
        $availability->getStart()->willReturn($start);
        $availability->getEnd()->willReturn($end);
        $availability->getStock()->willReturn(null);
        $availability->isStopSale()->willReturn(false);
        $start->format('Y-m-d')->willReturn('formatted_date_start');
        $end->modify('+1 day')->shouldBeCalled()->willReturn($end);
        $end->format('Y-m-d')->willReturn('formatted_date_end');


        $availability1->getStart()->willReturn($start1);
        $availability1->getEnd()->willReturn($end1);
        $availability1->getStock()->willReturn(0);
        $availability1->isStopSale()->willReturn(false);
        $start1->format('Y-m-d')->willReturn('formatted_date_2_start');
        $end1->modify('+1 day')->shouldBeCalled()->willReturn($end1);
        $end1->format('Y-m-d')->willReturn('formatted_date_2_end');

        $availability->getProduct()->willReturn($product);
        $exist->getStock()->willReturn(10);
        $availabilityRepository->findOneBy(
            [
                'date' => $start,
                'product' => $product
            ]
        )->willReturn($exist);

        $this
            ->normalize($productAvailability)
            ->shouldBe(
                [
                    [
                        'dateStart' => 'formatted_date_start',
                        'dateEnd'   => 'formatted_date_end',
                        'stock'     => 10
                    ],
                    [
                        'dateStart' => 'formatted_date_2_start',
                        'dateEnd'   => 'formatted_date_2_end',
                        'stock'     => 0
                    ],
                ]
            );
    }

    function it_denormalizes_product_availability(
        ProductLoader $productLoader,
        ProductAvailabilityFactory $productAvailabilityFactory,
        AvailabilityNormalizer $availabilityNormalizer,
        PartnerInterface $partner,
        ProductInterface $product,
        ProductAvailability $productAvailability,
        Availability $availability,
        Availability $availability1
    )
    {
        $productLoader->find($partner, 'product_id')->willReturn($product);
        $productAvailabilityFactory->create($product)->willReturn($productAvailability);
        $productAvailability->setPartner($partner)->shouldBeCalled()->willReturn($productAvailability);

        $availabilityNormalizer
            ->denormalize(
                $dataStock = (object) ['availability' => 'data'],
                ['product' => $product]
            )
            ->shouldBeCalled()
            ->willReturn($availability);

        $productAvailability->addAvailability($availability)->shouldBeCalled();


        $availabilityNormalizer
            ->denormalize(
                $dataStock1 = (object) ['availability' => 'data2'],
                ['product' => $product]
            )
            ->shouldBeCalled()
            ->willReturn($availability1);

        $productAvailability->addAvailability($availability1)->shouldBeCalled();

        $this
            ->denormalize(
                (object) [
                    'roomTypeCode' => 'product_id',
                    'stocks'       => [
                        $dataStock,
                        $dataStock1
                    ]
                ],
                ['partner' => $partner]
            )
            ->shouldBe($productAvailability);
    }


    function it_supports_denormalization_product_availability()
    {
        $this->supportsDenormalization(ProductAvailability::class)->shouldBe(true);
        $this->supportsDenormalization(\stdClass::class)->shouldBe(false);
    }


    function it_supports_normalization()
    {
        $this->supportsNormalization(ProductAvailability::class)->shouldBe(true);
        $this->supportsNormalization(\stdClass::class)->shouldBe(false);
    }
}

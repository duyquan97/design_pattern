<?php

namespace spec\App\Service\EAI\Serializer;

use App\Entity\Partner;
use App\Entity\Product;
use App\Repository\AvailabilityRepository;
use App\Model\Availability;
use App\Model\ProductAvailability;
use App\Service\EAI\Serializer\ProductAvailabilityNormalizer;
use PhpSpec\ObjectBehavior;

class ProductAvailabilityNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductAvailabilityNormalizer::class);
    }

    function let(AvailabilityRepository $availabilityRepository)
    {
        $this->beConstructedWith($availabilityRepository);
    }

    function it_normalizes_from_product_availability(
        ProductAvailability $productAvailability,
        Product $product,
        Partner $partner,
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

        $product->getIdentifier()->willReturn('product_id');
        $partner->getIdentifier()->willReturn('partner_id');
        $product->getPartner()->willReturn($partner);

        $productAvailability->getProduct()->willReturn($product);

        $availability->getStart()->willReturn($start);
        $availability->getEnd()->willReturn($end);
        $availability->getStock()->willReturn(2);
        $availability->isStopSale()->willReturn(false);
        $start->format('Y-m-d\\TH:i:s.P')->willReturn('formatted_date_start');
        $end->modify('+1 day')->shouldBeCalled()->willReturn($end);
        $end->format('Y-m-d\\TH:i:s.P')->willReturn('formatted_date_end');


        $availability1->getStart()->willReturn($start1);
        $availability1->getEnd()->willReturn($end1);
        $availability1->getStock()->willReturn(5);
        $availability1->isStopSale()->willReturn(false);
        $start1->format('Y-m-d\\TH:i:s.P')->willReturn('formatted_date_2_start');
        $end1->modify('+1 day')->shouldBeCalled()->willReturn($end1);
        $end1->format('Y-m-d\\TH:i:s.P')->willReturn('formatted_date_2_end');

        $data = $this->normalize($productAvailability);

        $data->shouldBeArray();
        $data[0]['rateBand']->shouldBe(
            [
                'code' => 'SBX',
                'partner' => [
                    'id' => 'partner_id'
                ],
            ]
        );
        $data[0]['product']->shouldBe(
            [
                'id' => 'product_id',
            ]
        );

        $data[0]['dateFrom']->shouldBe('formatted_date_start');
        $data[0]['dateTo']->shouldBe('formatted_date_end');
        $data[0]['quantity']->shouldBe(2);
    }

    function it_normalizes_from_product_zero_availability(
        ProductAvailability $productAvailability,
        Product $product,
        Partner $partner,
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

        $product->getIdentifier()->willReturn('product_id');
        $partner->getIdentifier()->willReturn('partner_id');
        $product->getPartner()->willReturn($partner);

        $productAvailability->getProduct()->willReturn($product);

        $availability->getStart()->willReturn($start);
        $availability->getEnd()->willReturn($end);
        $availability->getStock()->willReturn(0);
        $availability->isStopSale()->willReturn(false);
        $start->format('Y-m-d\\TH:i:s.P')->willReturn('formatted_date_start');
        $end->modify('+1 day')->shouldBeCalled()->willReturn($end);
        $end->format('Y-m-d\\TH:i:s.P')->willReturn('formatted_date_end');


        $availability1->getStart()->willReturn($start1);
        $availability1->getEnd()->willReturn($end1);
        $availability1->getStock()->willReturn(0);
        $availability1->isStopSale()->willReturn(false);
        $start1->format('Y-m-d\\TH:i:s.P')->willReturn('formatted_date_2_start');
        $end1->modify('+1 day')->shouldBeCalled()->willReturn($end1);
        $end1->format('Y-m-d\\TH:i:s.P')->willReturn('formatted_date_2_end');

        $data = $this->normalize($productAvailability);

        $data->shouldBeArray();

        $data[0]['quantity']->shouldBe(0);
        $data[1]['quantity']->shouldBe(0);
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

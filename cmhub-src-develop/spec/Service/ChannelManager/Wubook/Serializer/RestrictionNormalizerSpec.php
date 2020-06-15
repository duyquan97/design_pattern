<?php

namespace spec\App\Service\ChannelManager\Wubook\Serializer;

use App\Exception\DateFormatException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\Availability;
use App\Model\Factory\AvailabilityFactory;
use App\Model\PartnerInterface;
use App\Model\ProductAvailability;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductInterface;
use App\Service\ChannelManager\Wubook\Serializer\RestrictionNormalizer;
use App\Service\Loader\ProductLoader;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

class RestrictionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RestrictionNormalizer::class);
    }

    function let(AvailabilityFactory $availabilityFactory, ProductLoader $productLoader)
    {
        $this->beConstructedWith($availabilityFactory, $productLoader);
    }

    function it_doesnt_normalize(PartnerInterface $partner)
    {
        $request = [
            "dfrom" => "2019-01-01",
            "dto" => "2019-01-02",
            "room_id" => "12",
            "rate_id" => "1",
            "closed" => true,
            "cta" => true,
            "ctd" => false,
            "minstay" => 1,
            "maxstay" => 0,
            "minstayarr" => 1
        ];

        $this->shouldThrow(MethodNotImplementedException::class)->during('normalize', [json_encode($request), ['partner' => $partner]]);
    }

    function it_denormalizes_valid_data(Availability $availability, Availability $availability1, ProductInterface $product, ProductLoader $productLoader, PartnerInterface $partner, ProductAvailabilityCollection $productAvailabilityCollection)
    {
        $request = [
            "dfrom" => "2019-01-01",
            "dto" => "2019-01-02",
            "room_id" => "12",
            "rate_id" => "1",
            "closed" => true,
            "cta" => true,
            "ctd" => false,
            "minstay" => 1,
            "maxstay" => 0,
            "minstayarr" => 1
        ];

        $productLoader->find($partner, '12')->shouldBeCalled()->willReturn($product);
        $productAvailabilityCollection->getByProductAndDate($product,Argument::that(function(\DateTime $start) {
            return '2019-01-01' === $start->format('Y-m-d');
        }))->shouldBeCalled()->willReturn($availability);
        $productAvailabilityCollection->getByProductAndDate($product,Argument::that(function(\DateTime $start) {
            return '2019-01-02' === $start->format('Y-m-d');
        }))->shouldBeCalled()->willReturn($availability1);
        $availability->setStopSale(true)->shouldBeCalled()->willReturn($availability);
        $availability1->setStopSale(true)->shouldBeCalled()->willReturn($availability1);
        $productAvailabilityCollection->addAvailability($availability)->shouldBeCalled();
        $productAvailabilityCollection->addAvailability($availability1)->shouldBeCalled();

        $this->denormalize(json_decode(json_encode($request)), ['partner' => $partner, 'collection' => $productAvailabilityCollection])->shouldBe($productAvailabilityCollection);
    }

    function it_doesnt_denormalize_an_empty_start_date(PartnerInterface $partner, ProductAvailabilityCollection $productAvailabilityCollection)
    {
        $request = [
            "dto" => "2019-01-02",
            "room_id" => "366455",
            "rate_id" => "1",
            "closed" => true,
            "cta" => true,
            "ctd" => false,
            "minstay" => 1,
            "maxstay" => 0,
            "minstayarr" => 1
        ];

        $this->shouldThrow(ValidationException::class)->during('denormalize', [json_decode(json_encode($request)), ['partner' => $partner, 'collection' => $productAvailabilityCollection]]);
    }

    function it_doesnt_denormalize_an_empty_end_date(PartnerInterface $partner, ProductAvailabilityCollection $productAvailabilityCollection)
    {
        $request = [
            "dfrom" => "2019-01-01",
            "room_id" => "366455",
            "rate_id" => "1",
            "closed" => true,
            "cta" => true,
            "ctd" => false,
            "minstay" => 1,
            "maxstay" => 0,
            "minstayarr" => 1
        ];

        $this->shouldThrow(ValidationException::class)->during('denormalize', [json_decode(json_encode($request)), ['partner' => $partner, 'collection' => $productAvailabilityCollection]]);
    }

    function it_doesnt_denormalize_a_wrong_start_date_format(PartnerInterface $partner, ProductAvailabilityCollection $productAvailabilityCollection)
    {
        $request = [
            "dfrom" => "2019-01-",
            "dto" => "2019-01-02",
            "room_id" => "366455",
            "rate_id" => "1",
            "closed" => true,
            "cta" => true,
            "ctd" => false,
            "minstay" => 1,
            "maxstay" => 0,
            "minstayarr" => 1
        ];

        $this->shouldThrow(DateFormatException::class)->during('denormalize', [json_decode(json_encode($request)), ['partner' => $partner, 'collection' => $productAvailabilityCollection]]);
    }

    function it_doesnt_denormalize_a_wrong_end_date_format(PartnerInterface $partner, ProductAvailabilityCollection $productAvailabilityCollection)
    {
        $request = [
            "dfrom" => "2019-01-01",
            "dto" => "2019-01-",
            "room_id" => "366455",
            "rate_id" => "1",
            "closed" => true,
            "cta" => true,
            "ctd" => false,
            "minstay" => 1,
            "maxstay" => 0,
            "minstayarr" => 1
        ];

        $this->shouldThrow(DateFormatException::class)->during('denormalize', [json_decode(json_encode($request)), ['partner' => $partner, 'collection' => $productAvailabilityCollection]]);
    }

    function it_doesnt_denormalize_a_greater_end_date(PartnerInterface $partner, ProductAvailabilityCollection $productAvailabilityCollection)
    {
        $request = [
            "dfrom" => "2019-01-10",
            "dto" => "2019-01-02",
            "room_id" => "366455",
            "rate_id" => "1",
            "closed" => true,
            "cta" => true,
            "ctd" => false,
            "minstay" => 1,
            "maxstay" => 0,
            "minstayarr" => 1
        ];

        $this->shouldThrow(ValidationException::class)->during('denormalize', [json_decode(json_encode($request)), ['partner' => $partner, 'collection' => $productAvailabilityCollection]]);
    }

    function it_doesnt_denormalize_room_not_defined(PartnerInterface $partner, ProductAvailabilityCollection $productAvailabilityCollection)
    {
        $request = [
            "dfrom" => "2019-01-01",
            "dto" => "2019-01-02",
            "rate_id" => "1",
            "closed" => true,
            "cta" => true,
            "ctd" => false,
            "minstay" => 1,
            "maxstay" => 0,
            "minstayarr" => 1
        ];

        $this->shouldThrow(ValidationException::class)->during('denormalize', [json_decode(json_encode($request)), ['partner' => $partner, 'collection' => $productAvailabilityCollection]]);
    }

    function it_doesnt_denormalize_invalid_product(PartnerInterface $partner, ProductAvailabilityCollection $productAvailabilityCollection)
    {
        $request = [
            "dfrom" => "2019-01-01",
            "dto" => "2019-01-02",
            "room_id" => "",
            "rate_id" => "1",
            "closed" => true,
            "cta" => true,
            "ctd" => false,
            "minstay" => 1,
            "maxstay" => 0,
            "minstayarr" => 1
        ];

        $this->shouldThrow(ProductNotFoundException::class)->during('denormalize', [json_decode(json_encode($request)), ['partner' => $partner, 'collection' => $productAvailabilityCollection]]);
    }
    
    function it_doesnt_support_normalization_for()
    {
        $this->supportsNormalization(Availability::class)->shouldBe(false);
    }

    function it_supports_denormalization_for()
    {
        $this->supportsDenormalization(Availability::class)->shouldBe(true);
    }

    function it_doesnt_support_denormalization_for()
    {
        $this->supportsDenormalization(ProductAvailability::class)->shouldBe(false);
    }
}

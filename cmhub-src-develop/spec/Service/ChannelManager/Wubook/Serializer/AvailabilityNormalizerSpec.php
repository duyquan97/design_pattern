<?php

namespace spec\App\Service\ChannelManager\Wubook\Serializer;

use App\Exception\DateFormatException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\Availability;
use App\Model\Factory\AvailabilityFactory;
use App\Model\PartnerInterface;
use App\Model\ProductAvailability;
use App\Model\ProductInterface;
use App\Service\ChannelManager\Wubook\Serializer\AvailabilityNormalizer;
use App\Service\Loader\ProductLoader;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

class AvailabilityNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AvailabilityNormalizer::class);
    }

    function let(AvailabilityFactory $availabilityFactory, ProductLoader $productLoader)
    {
        $this->beConstructedWith($availabilityFactory, $productLoader);
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

    function it_denormalizes_valid_data(Availability $availability, ProductInterface $product, AvailabilityFactory $availabilityFactory, ProductLoader $productLoader, PartnerInterface $partner)
    {
        $request = [
            "dfrom" => "2019-01-01",
            "dto" => "2019-01-12",
            "avail" => "1",
            "room_id" => "12"
        ];

        $productLoader->find($partner, '12')->shouldBeCalled()->willReturn($product);
        $availabilityFactory->create(
            Argument::that(function(\DateTime $start) {
                return '2019-01-01' === $start->format('Y-m-d');
            }),
            Argument::that(function(\DateTime $start) {
                return '2019-01-12' === $start->format('Y-m-d');
            }),
            1,
            $product
        )->shouldBeCalled()->willReturn($availability);

        $this->denormalize(json_decode(json_encode($request)), ['partner' => $partner])->shouldBe($availability);
    }

    function it_doesnt_denormalize_an_empty_start_date(ProductInterface $product)
    {
        $request = [
            "dto" => "2019-01-12",
            "avail" => "1"
        ];

        $this->shouldThrow(ValidationException::class)->during('denormalize', [json_decode(json_encode($request)), ['product' => $product]]);
    }

    function it_doesnt_denormalize_an_empty_end_date(ProductInterface $product)
    {
        $request = [
            "dfrom" => "2019-01-12",
            "avail" => "1"
        ];

        $this->shouldThrow(ValidationException::class)->during('denormalize', [json_decode(json_encode($request)), ['product' => $product]]);
    }

    function it_doesnt_denormalize_a_wrong_start_date_format(ProductInterface $product)
    {
        $request = [
            "dfrom" => "2019-01-",
            "dto" => "2019-01-10",
            "avail" => "1"
        ];

        $this->shouldThrow(DateFormatException::class)->during('denormalize', [json_decode(json_encode($request)), ['product' => $product]]);
    }

    function it_doesnt_denormalize_a_wrong_end_date_format(ProductInterface $product)
    {
        $request = [
            "dfrom" => "2019-01-10",
            "dto" => "2019-01-",
            "avail" => "1"
        ];

        $this->shouldThrow(DateFormatException::class)->during('denormalize', [json_decode(json_encode($request)), ['product' => $product]]);
    }

    function it_doesnt_denormalize_a_greater_end_date(ProductInterface $product)
    {
        $request = [
            "dfrom" => "2019-01-10",
            "dto" => "2019-01-02",
            "avail" => "1"
        ];

        $this->shouldThrow(ValidationException::class)->during('denormalize', [json_decode(json_encode($request)), ['product' => $product]]);
    }

    function it_doesnt_denormalize_a_negative_amount(ProductInterface $product)
    {
        $request = [
            "dfrom" => "2019-01-01",
            "dto" => "2019-01-02",
            "avail" => "-1"
        ];

        $this->shouldThrow(ValidationException::class)->during('denormalize', [json_decode(json_encode($request)), ['product' => $product]]);
    }

    function it_doesnt_denormalize_room_not_defined(PartnerInterface $partner)
    {
        $request = [
            "dfrom" => "2019-01-01",
            "dto" => "2019-01-02",
            "avail" => "1"
        ];

        $this->shouldThrow(ValidationException::class)->during('denormalize', [json_decode(json_encode($request)), ['partner' => $partner]]);
    }

    function it_doesnt_denormalize_invalid_product(PartnerInterface $partner)
    {
        $request = [
            "dfrom" => "2019-01-01",
            "dto" => "2019-01-02",
            "avail" => "1",
            "room_id" => ""
        ];

        $this->shouldThrow(ProductNotFoundException::class)->during('denormalize', [json_decode(json_encode($request)), ['partner' => $partner]]);
    }

    function it_doesnt_denormalize_without_stock(PartnerInterface $partner)
    {
        $request = [
            "dfrom" => "2019-01-01",
            "dto" => "2019-01-02",
            "room_id" => ""
        ];

        $this->shouldThrow(ValidationException::class)->during('denormalize', [json_decode(json_encode($request)), ['partner' => $partner]]);
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

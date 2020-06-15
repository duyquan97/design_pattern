<?php

namespace spec\App\Service\ChannelManager\Wubook\Serializer;

use App\Exception\DateFormatException;
use App\Exception\ValidationException;
use App\Model\Factory\RateFactory;
use App\Model\PartnerInterface;
use App\Model\ProductInterface;
use App\Model\ProductRate;
use App\Model\Rate;
use App\Service\ChannelManager\Wubook\Serializer\RateNormalizer;
use App\Service\Loader\ProductLoader;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

class RateNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RateNormalizer::class);
    }

    function let(RateFactory $rateFactory, ProductLoader $productLoader)
    {
        $this->beConstructedWith($rateFactory, $productLoader);
    }

    function it_doesnt_normalize(PartnerInterface $partner)
    {
        $request = [
            "dfrom" => "2019-01-12",
            "dto" => "2019-01-24",
            "price" => "1"
        ];

        $this->shouldThrow(MethodNotImplementedException::class)->during('normalize', [json_encode($request), ['partner' => $partner]]);
    }

    function it_denormalizes_valid_data(ProductInterface $product, PartnerInterface $partner, RateFactory $rateFactory, Rate $rate)
    {
        $request = [
            "dfrom" => "2019-01-01",
            "dto" => "2019-01-12",
            "room_id" => "366455",
            "rate_id" => "SBX",
            "price" => 90.15
        ];

        $rateFactory->create(
            Argument::that(function(\DateTime $start) {
                return '2019-01-01' === $start->format('Y-m-d');
            }),
            Argument::that(function(\DateTime $start) {
                return '2019-01-12' === $start->format('Y-m-d');
            }),
            90.15,
            $product
        )->shouldBeCalled()->willReturn($rate);

        $this->denormalize(json_decode(json_encode($request)), ['product' => $product])->shouldBe($rate);
    }

    function it_doesnt_denormalize_an_empty_start_date(PartnerInterface $partner)
    {
        $request = [
            "dto" => "2019-01-12",
            "price" => "1"
        ];

        $this->shouldThrow(ValidationException::class)->during('denormalize', [json_decode(json_encode($request)), ['partner' => $partner]]);
    }

    function it_doesnt_denormalize_an_empty_end_date(PartnerInterface $partner)
    {
        $request = [
            "dfrom" => "2019-01-12",
            "price" => "1"
        ];

        $this->shouldThrow(ValidationException::class)->during('denormalize', [json_decode(json_encode($request)), ['partner' => $partner]]);
    }

    function it_doesnt_denormalize_a_wrong_start_date_format(PartnerInterface $partner)
    {
        $request = [
            "dfrom" => "2019-01-",
            "dto" => "2019-01-10",
            "price" => "1"
        ];

        $this->shouldThrow(DateFormatException::class)->during('denormalize', [json_decode(json_encode($request)), ['partner' => $partner]]);
    }

    function it_doesnt_denormalize_a_wrong_end_date_format(PartnerInterface $partner)
    {
        $request = [
            "dfrom" => "2019-01-10",
            "dto" => "2019-01-",
            "price" => "1"
        ];

        $this->shouldThrow(DateFormatException::class)->during('denormalize', [json_decode(json_encode($request)), ['partner' => $partner]]);
    }

    function it_doesnt_denormalize_a_greater_end_date(PartnerInterface $partner)
    {
        $request = [
            "dfrom" => "2019-01-10",
            "dto" => "2019-01-02",
            "price" => "1"
        ];

        $this->shouldThrow(ValidationException::class)->during('denormalize', [json_decode(json_encode($request)), ['partner' => $partner]]);
    }

    function it_doesnt_denormalize_a_negative_amount(PartnerInterface $partner)
    {
        $request = [
            "dfrom" => "2019-01-01",
            "dto" => "2019-01-02",
            "price" => "-1"
        ];

        $this->shouldThrow(ValidationException::class)->during('denormalize', [json_decode(json_encode($request)), ['partner' => $partner]]);
    }

    function it_throws_exception_if_price_is_not_defined(PartnerInterface $partner)
    {
        $request = [
            "dfrom" => "2019-01-01",
            "dto" => "2019-01-02",
        ];

        $this->shouldThrow(ValidationException::class)->during('denormalize', [json_decode(json_encode($request)), ['partner' => $partner]]);
    }

    function it_doesnt_support_normalization_for()
    {
        $this->supportsNormalization(Rate::class)->shouldBe(false);
    }

    function it_supports_denormalization_for()
    {
        $this->supportsDenormalization(Rate::class)->shouldBe(true);
    }

    function it_doesnt_support_denormalization_for()
    {
        $this->supportsDenormalization(ProductRate::class)->shouldBe(false);
    }
}

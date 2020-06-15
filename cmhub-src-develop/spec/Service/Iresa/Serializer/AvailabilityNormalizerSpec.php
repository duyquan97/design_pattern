<?php

namespace spec\App\Service\Iresa\Serializer;

use App\Model\Availability;
use App\Model\Factory\AvailabilityFactory;
use App\Model\ProductInterface;
use App\Service\Iresa\Serializer\AvailabilityNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AvailabilityNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AvailabilityNormalizer::class);
    }

    function let(AvailabilityFactory $availabilityFactory)
    {
        $this->beConstructedWith($availabilityFactory);
    }

    function it_denormalizes_availability_object(AvailabilityFactory $availabilityFactory, Availability $availability, ProductInterface $product)
    {
        $availabilityFactory
            ->create(
                Argument::that(function (\DateTime $start) {
                    return '2018-09-09' === $start->format('Y-m-d');
                }),
                Argument::that(function (\DateTime $start) {
                    return '2018-09-09' === $start->format('Y-m-d');
                }),
                10,
                $product
            )
            ->shouldBeCalled()
            ->willReturn($availability);

        $this
            ->denormalize(
                (object) [
                    'date'  => '2018-09-09T00:00:00.0000000+01:00',
                    'stock' => 10
                ],
                ['product' => $product]
            );
    }

    function it_only_supports_availability_object_normalization()
    {
        $this->supportsNormalization(Availability::class)->shouldBe(true);
        $this->supportsNormalization(\stdClass::class)->shouldBe(false);
    }

    function it_only_supports_availability_object_denormalization()
    {
        $this->supportsDenormalization(Availability::class)->shouldBe(true);
        $this->supportsDenormalization(\stdClass::class)->shouldBe(false);
    }
}

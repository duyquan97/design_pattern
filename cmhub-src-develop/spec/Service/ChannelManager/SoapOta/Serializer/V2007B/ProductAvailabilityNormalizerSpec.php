<?php

namespace spec\App\Service\ChannelManager\SoapOta\Serializer\V2007B;

use App\Service\ChannelManager\SoapOta\Serializer\V2007B\ProductAvailabilityNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductAvailabilityNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductAvailabilityNormalizer::class);
    }
}

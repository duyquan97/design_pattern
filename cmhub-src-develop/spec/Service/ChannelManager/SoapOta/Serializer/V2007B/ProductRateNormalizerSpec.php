<?php

namespace spec\App\Service\ChannelManager\SoapOta\Serializer\V2007B;

use App\Service\ChannelManager\SoapOta\Serializer\V2007B\ProductRateNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductRateNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRateNormalizer::class);
    }
}

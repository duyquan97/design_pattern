<?php

namespace spec\App\Service\ChannelManager;

use App\Entity\ChannelManager;
use App\Exception\ChannelManagerNotSupportedException;
use App\Service\ChannelManager\ChannelManagerInterface;
use App\Service\ChannelManager\ChannelManagerResolver;
use PhpSpec\ObjectBehavior;

class ChannelManagerResolverSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ChannelManagerResolver::class);
    }

    function let(ChannelManagerInterface $integration1, ChannelManagerInterface $integration2)
    {
        $this->beConstructedWith(
            [
                $integration1,
                $integration2
            ]
        );
    }

    function it_gets_the_integration_if_exists(ChannelManager $channelManager, ChannelManagerInterface $integration1, ChannelManagerInterface $integration2)
    {
        $channelManager->getIdentifier()->willReturn('cm');
        $integration1->supports('cm')->willReturn(false);
        $integration2->supports('cm')->willReturn(true);
        $this->getIntegration($channelManager)->shouldBe($integration2);
    }

    function it_throws_exception_if_integration_does_not_exists(ChannelManager $channelManager, ChannelManagerInterface $integration1, ChannelManagerInterface $integration2)
    {
        $channelManager->getIdentifier()->willReturn('cm');
        $integration1->supports('cm')->willReturn(false);
        $integration2->supports('cm')->willReturn(false);
        $this->shouldThrow(ChannelManagerNotSupportedException::class)->during('getIntegration', [$channelManager]);
    }
}

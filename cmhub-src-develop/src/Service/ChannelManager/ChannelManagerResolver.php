<?php

namespace App\Service\ChannelManager;

use App\Entity\ChannelManager;
use App\Exception\ChannelManagerNotSupportedException;

/**
 * Interface ChannelManagerInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 **/
class ChannelManagerResolver
{
    /**
     *
     * @var ChannelManagerInterface[]
     */
    private $channelManagerIntegrations;

    /**
     * ChannelManagerResolver constructor.
     *
     * @param array $channelManagerIntegrations
     */
    public function __construct(array $channelManagerIntegrations)
    {
        $this->channelManagerIntegrations = $channelManagerIntegrations;
    }

    /**
     *
     * @param ChannelManager $channelManager
     *
     * @return ChannelManagerInterface
     *
     * @throws ChannelManagerNotSupportedException
     */
    public function getIntegration(ChannelManager $channelManager): ChannelManagerInterface
    {
        foreach ($this->channelManagerIntegrations as $integration) {
            if ($integration->supports($channelManager->getIdentifier())) {
                return $integration;
            }
        }

        throw new ChannelManagerNotSupportedException();
    }
}

<?php

namespace App\Entity;

use App\Repository\ChannelManagerRepository;

/**
 * Class TransactionChannel
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class TransactionChannel
{
    public const EAI = 'eai';

    public const IRESA = 'iresa';

    public const CHOICES = [
        'EAI'   => self::EAI,
        'Iresa' => self::IRESA,
    ];

    public const HAS_CALLBACK = [
        self::EAI,
    ];

    /**
     * @var ChannelManagerRepository $channelManagerRepository
     */
    private $channelManagerRepository;

    /**
     * TransactionChannel constructor.
     *
     * @param ChannelManagerRepository $channelManagerRepository
     */
    public function __construct(ChannelManagerRepository $channelManagerRepository)
    {
        $this->channelManagerRepository = $channelManagerRepository;
    }

    /**
     * @return array
     */
    public function getChannels()
    {
        $channelManagers = $this->channelManagerRepository->findAll();
        $channelChoices = self::CHOICES;
        /* @var ChannelManager $channelManager */
        foreach ($channelManagers as $channelManager) {
            $channelChoices[$channelManager->getName()] = $channelManager->getIdentifier();
        }

        return $channelChoices;
    }
}

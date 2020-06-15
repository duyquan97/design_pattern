<?php

namespace App\MessageHandler;

use App\Entity\Partner;
use App\Exception\SynchronizerNotFoundException;
use App\Message\SyncData;
use App\Repository\PartnerRepository;
use App\Service\Synchronizer\DataSynchronizationManager;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class SyncDataHandler
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class SyncDataHandler implements MessageHandlerInterface
{
    /**
     * @var partnerRepository
     */
    private $partnerRepository;

    /**
     * @var DataSynchronizationManager
     */
    private $synchronizationManager;

    /**
     * SyncDataHandler constructor.
     *
     * @param PartnerRepository          $partnerRepository
     * @param DataSynchronizationManager $synchronizationManager
     */
    public function __construct(PartnerRepository $partnerRepository, DataSynchronizationManager $synchronizationManager)
    {
        $this->partnerRepository = $partnerRepository;
        $this->synchronizationManager = $synchronizationManager;
    }

    /**
     *
     * @param SyncData $message
     *
     * @return void
     *
     * @throws SynchronizerNotFoundException
     */
    public function __invoke(SyncData $message)
    {
        /* @var Partner $partner */
        $partner = $this->partnerRepository->findOneBy(['identifier' => $message->getIdentifier()]);
        if (!$partner) {
            throw new UnrecoverableMessageHandlingException(sprintf('Partner with identifier `%s` has not been found in `%s`', $message->getIdentifier(), self::class));
        }

        $this->synchronizationManager->sync($partner, $message->getStart(), $message->getEnd(), $message->getType());
    }
}

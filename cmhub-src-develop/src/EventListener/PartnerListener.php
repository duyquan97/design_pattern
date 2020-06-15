<?php

namespace App\EventListener;

use App\Entity\Partner;
use App\Entity\PartnerStatus;
use App\Message\Factory\PartnerChannelManagerUpdatedFactory;
use App\Message\Factory\PartnerUpdatedFactory;
use App\Service\ChannelManager\ChannelManagerList;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class PartnerListener
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PartnerListener
{
    /**
     * @var MessageBusInterface $messageBus
     */
    private $messageBus;

    /**
     * @var PartnerChannelManagerUpdatedFactory $messageFactory
     */
    private $messageFactory;

    /**
     * @var string $partnerIdentifier
     */
    private $partnerIdentifier;

    /**
     * @var PartnerUpdatedFactory $partnerUpdatedFactory
     */
    private $partnerUpdatedFactory;

    /**
     * PartnerListener constructor.
     *
     * @param MessageBusInterface                 $messageBus
     * @param PartnerChannelManagerUpdatedFactory $messageFactory
     * @param PartnerUpdatedFactory               $partnerUpdatedFactory
     */
    public function __construct(MessageBusInterface $messageBus, PartnerChannelManagerUpdatedFactory $messageFactory, PartnerUpdatedFactory $partnerUpdatedFactory)
    {
        $this->messageBus = $messageBus;
        $this->messageFactory = $messageFactory;
        $this->partnerUpdatedFactory = $partnerUpdatedFactory;
    }

    /**
     * @param PreUpdateEventArgs $eventArgs
     *
     * @return void
     */
    public function preUpdate(PreUpdateEventArgs $eventArgs): void
    {
        $partner = $eventArgs->getEntity();

        if (!$partner instanceof Partner) {
            return;
        }

        if ($eventArgs->hasChangedField('enabled') && !$partner->isEnabled()) {
            $partner->setConnectedAt(null);
        }

        if ($eventArgs->hasChangedField('status') && PartnerStatus::CEASED === $partner->getStatus()) {
            $partner->setConnectedAt(null);
        }

        if ($eventArgs->hasChangedField('channelManager')) {
            $partner->setConnectedAt(null);
            $channelManager = $partner->getChannelManager();
            if ($channelManager && !$channelManager->hasPartnerLevelAuth()) {
                $partner->setUser(null);
            }

            if ($channelManager && ChannelManagerList::BB8 !== $channelManager->getIdentifier()) {
                $this->partnerIdentifier = $partner->getIdentifier();
            }
        }
    }

    /**
     * @param PostFlushEventArgs $args
     *
     * @return void
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        if ($this->partnerIdentifier) {
            $this->messageBus->dispatch($this->messageFactory->create($this->partnerIdentifier));
        }
    }

    /**
     * Gets all the entities to flush
     *
     * @param LifecycleEventArgs $eventArgs Event args
     *
     * @return void
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $partner = $eventArgs->getObject();
        if (!$partner instanceof Partner) {
            return;
        }

        $this->dispatchPartnerUpdatedMessage($partner);
    }

    /**
     * Gets all the entities to flush
     *
     * @param LifecycleEventArgs $eventArgs Event args
     *
     * @return void
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $partner = $eventArgs->getObject();
        if (!$partner instanceof Partner) {
            return;
        }

        $this->dispatchPartnerUpdatedMessage($partner);
    }

    /**
     *
     * @param Partner $partner
     *
     * @return void
     */
    private function dispatchPartnerUpdatedMessage(Partner $partner)
    {
        if ($partner->isEnabled()) {
            $this->messageBus->dispatch($this->partnerUpdatedFactory->create($partner->getIdentifier()));
        }
    }
}

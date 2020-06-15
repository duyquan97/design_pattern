<?php

namespace spec\App\EventListener;

use App\Entity\ChannelManager;
use App\Entity\Partner;
use App\Entity\PartnerStatus;
use App\EventListener\PartnerListener;
use App\Message\Factory\PartnerChannelManagerUpdatedFactory;
use App\Message\Factory\PartnerUpdatedFactory;
use App\Message\PartnerChannelManagerUpdated;
use App\Message\PartnerUpdated;
use App\Service\ChannelManager\ChannelManagerList;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class PartnerListenerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PartnerListener::class);
    }

    function let(
        MessageBusInterface $messageBus, PartnerChannelManagerUpdatedFactory $messageFactory, PartnerUpdatedFactory $partnerUpdatedFactory
    )
    {
        $this->beConstructedWith($messageBus, $messageFactory, $partnerUpdatedFactory);
    }

    function it_partner_is_disabled(Partner $partner, PreUpdateEventArgs $eventArgs)
    {
        $eventArgs->getEntity()->willReturn($partner);
        $eventArgs->hasChangedField('enabled')->willReturn(true);
        $eventArgs->hasChangedField('status')->willReturn(false);
        $eventArgs->hasChangedField('channelManager')->willReturn(false);
        $partner->isEnabled()->willReturn(false);
        $partner->getStatus()->willReturn(PartnerStatus::PARTNER);
        $partner->setConnectedAt(null)->shouldBeCalled()->willReturn($partner);
        $this->preUpdate($eventArgs);

    }

    function it_partner_is_ceased(Partner $partner, PreUpdateEventArgs $eventArgs)
    {
        $eventArgs->getEntity()->willReturn($partner);
        $eventArgs->hasChangedField('enabled')->willReturn(false);
        $eventArgs->hasChangedField('status')->willReturn(true);
        $eventArgs->hasChangedField('channelManager')->willReturn(false);
        $partner->isEnabled()->willReturn(true);
        $partner->getStatus()->willReturn(PartnerStatus::CEASED);
        $partner->setConnectedAt(null)->shouldBeCalled()->willReturn($partner);
        $this->preUpdate($eventArgs);

    }

    function new_cm_is_not_bb8(Partner $partner, PreUpdateEventArgs $eventArgs, ChannelManager $channelManager, PostFlushEventArgs $flushEventArgs, MessageBusInterface $messageBus, PartnerChannelManagerUpdatedFactory $messageFactory, PartnerChannelManagerUpdated $message)
    {
        $eventArgs->getEntity()->willReturn($partner);
        $eventArgs->hasChangedField('enabled')->willReturn(false);
        $eventArgs->hasChangedField('status')->willReturn(false);
        $eventArgs->hasChangedField('channelManager')->willReturn(true);
        $partner->isEnabled()->willReturn(true);
        $partner->getStatus()->willReturn(PartnerStatus::PARTNER);
        $partner->setConnectedAt(null)->shouldBeCalled()->willReturn($partner);
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->hasPartnerLevelAuth()->willReturn(false);
        $partner->setUser(null)->shouldBeCalled()->willReturn($partner);
        $channelManager->getIdentifier()->willReturn(ChannelManagerList::SITEMINDER);
        $partner->getIdentifier()->willReturn('235854');
        $this->preUpdate($eventArgs);

        $messageFactory->create('235854')->willReturn($message);
        $messageBus->dispatch($message)->shouldBeCalled()->willReturn(new Envelope($message));
        $this->postFlush($flushEventArgs);

    }

    function it_partner_change_channelManager_to_bb8(Partner $partner, PreUpdateEventArgs $eventArgs, ChannelManager $channelManager, PostFlushEventArgs $flushEventArgs, MessageBusInterface $messageBus, PartnerChannelManagerUpdatedFactory $messageFactory, PartnerChannelManagerUpdated $message)
    {
        $eventArgs->getEntity()->willReturn($partner);
        $eventArgs->hasChangedField('enabled')->willReturn(false);
        $eventArgs->hasChangedField('status')->willReturn(false);
        $eventArgs->hasChangedField('channelManager')->willReturn(true);
        $partner->isEnabled()->willReturn(true);
        $partner->getStatus()->willReturn(PartnerStatus::PARTNER);
        $partner->setConnectedAt(null)->shouldBeCalled()->willReturn($partner);
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->hasPartnerLevelAuth()->willReturn(false);
        $partner->setUser(null)->shouldBeCalled()->willReturn($partner);
        $channelManager->getIdentifier()->willReturn(ChannelManagerList::BB8);
        $partner->getIdentifier()->shouldNotBeCalled();
        $this->preUpdate($eventArgs);

        $messageFactory->create('235854')->shouldNotBeCalled();
        $messageBus->dispatch($message)->shouldNotBeCalled();
        $this->postFlush($flushEventArgs);

    }

    function it_dispatches_partner_updated_message_post_update(Partner $partner, LifecycleEventArgs $eventArgs, MessageBusInterface $messageBus, PartnerUpdatedFactory $partnerUpdatedFactory, PartnerUpdated $message)
    {
        $eventArgs->getObject()->willReturn($partner);
        $partner->isEnabled()->willReturn(true);
        $partner->getIdentifier()->willReturn('identifier');
        $partnerUpdatedFactory->create('identifier')->willReturn($message);
        $messageBus->dispatch($message)->willReturn(new Envelope($message))->shouldBeCalled();
        $this->postUpdate($eventArgs);

    }

    function it_dispatches_partner_updated_message_post_persist(Partner $partner, LifecycleEventArgs $eventArgs, MessageBusInterface $messageBus, PartnerUpdatedFactory $partnerUpdatedFactory, PartnerUpdated $message)
    {
        $eventArgs->getObject()->willReturn($partner);
        $partner->isEnabled()->willReturn(true);
        $partner->getIdentifier()->willReturn('identifier');
        $partnerUpdatedFactory->create('identifier')->willReturn($message);
        $messageBus->dispatch($message)->willReturn(new Envelope($message))->shouldBeCalled();
        $this->postPersist($eventArgs);

    }

    function it_does_not_dispatch_partner_updated_message_post_update(Partner $partner, LifecycleEventArgs $eventArgs, MessageBusInterface $messageBus, PartnerUpdatedFactory $partnerUpdatedFactory, PartnerUpdated $message)
    {
        $eventArgs->getObject()->willReturn($partner);
        $partner->isEnabled()->willReturn(false);
        $messageBus->dispatch($message)->willReturn(new Envelope($message))->shouldNotBeCalled();
        $this->postUpdate($eventArgs);
    }

    function it_does_not_dispatch_partner_updated_message_post_persist(Partner $partner, LifecycleEventArgs $eventArgs, MessageBusInterface $messageBus, PartnerUpdatedFactory $partnerUpdatedFactory, PartnerUpdated $message)
    {
        $eventArgs->getObject()->willReturn($partner);
        $partner->isEnabled()->willReturn(false);
        $messageBus->dispatch($message)->willReturn(new Envelope($message))->shouldNotBeCalled();
        $this->postPersist($eventArgs);
    }
}

<?php

namespace spec\App\MessageHandler;

use App\Entity\Partner;
use App\Message\SyncData;
use App\MessageHandler\SyncDataHandler;
use App\Repository\PartnerRepository;
use App\Service\Synchronizer\AvailabilitySynchronizer;
use App\Service\Synchronizer\DataSynchronizationManager;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

class SyncDataHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SyncDataHandler::class);
    }

    function let(PartnerRepository $partnerRepository, DataSynchronizationManager $synchronizationManager)
    {
        $this->beConstructedWith($partnerRepository, $synchronizationManager);
    }

    function it_process_message(
        DataSynchronizationManager $synchronizationManager,
        PartnerRepository $partnerRepository,
        Partner $partner,
        SyncData $message
    )
    {
        $message->getIdentifier()->willReturn('my_id');
        $message->getStart()->willReturn($start = date_create());
        $message->getEnd()->willReturn($end = date_create('+3 year'));
        $message->getType()->willReturn(AvailabilitySynchronizer::TYPE);
        $partnerRepository->findOneBy(['identifier' => 'my_id'])->willReturn($partner);

        $synchronizationManager->sync($partner, $start, $end, AvailabilitySynchronizer::TYPE);
        $this->__invoke($message);
    }

    function it_process_message_failed(
        PartnerRepository $partnerRepository,
        SyncData $message
    )
    {
        $message->getIdentifier()->willReturn('my_id');
        $partnerRepository->findOneBy(['identifier' => 'my_id'])->willReturn(null);
        $this->shouldThrow(UnrecoverableMessageHandlingException::class)->during('__invoke', [$message]);
    }
}

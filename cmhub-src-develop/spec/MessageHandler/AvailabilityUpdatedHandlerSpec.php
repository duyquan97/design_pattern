<?php

namespace spec\App\MessageHandler;

use App\Entity\Availability;
use App\Entity\Factory\TransactionFactory;
use App\Entity\ImportData;
use App\Entity\Partner;
use App\Entity\Transaction;
use App\Entity\TransactionChannel;
use App\Entity\TransactionStatus;
use App\Entity\TransactionType;
use App\Message\Factory\TransactionScheduledFactory;
use App\Message\AvailabilityUpdated;
use App\Message\ImportDataFileUploaded;
use App\Message\TransactionScheduled;
use App\MessageHandler\AvailabilityUpdatedHandler;
use App\Repository\AvailabilityRepository;
use App\Repository\ImportDataRepository;
use App\Service\DataImport\ImportDataManager;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class AvailabilityUpdatedHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AvailabilityUpdatedHandler::class);
    }

    function let(AvailabilityRepository $availabilityRepository, EntityManagerInterface $entityManager, TransactionFactory $transactionFactory, MessageBusInterface $messageBus, TransactionScheduledFactory $messageFactory)
    {
        $this->beConstructedWith($availabilityRepository, $entityManager, $transactionFactory, $messageBus, $messageFactory);
    }

    function it_doesnt_process_message_if_entities_not_found(
        AvailabilityUpdated $message, EntityManagerInterface $entityManager, AvailabilityRepository $availabilityRepository
    )
    {
        $message->getAvailabilityIds()->willReturn([1,2]);
        $availabilityRepository->findBy(['id' => [1,2]])->willReturn([]);

        $this->shouldThrow(UnrecoverableMessageHandlingException::class)->during('__invoke', [$message]);
    }

    function it_process_message(AvailabilityUpdated $message, EntityManagerInterface $entityManager, AvailabilityRepository $availabilityRepository,
                                Availability $availability1, Availability $availability2, Partner $partner, Transaction $transaction, TransactionFactory $transactionFactory,
                                MessageBusInterface $messageBus, TransactionScheduledFactory $messageFactory, TransactionScheduled $transactionMessage)
    {
        $message->getAvailabilityIds()->willReturn([1,2]);
        $availabilityRepository->findBy(['id' => [1,2]])->willReturn([$availability1, $availability2]);
        $availability1->getPartner()->willReturn($partner);
        $message->getChannel()->willReturn('eai');
        $transactionFactory->create(
            TransactionType::AVAILABILITY,
            'eai',
            TransactionStatus::SCHEDULED,
            $partner
        )->willReturn($transaction);
        $transaction->getId()->willReturn(1);
        $entityManager->persist($transaction)->shouldBeCalled();
        $availability1->setTransaction($transaction)->shouldBeCalled();
        $availability2->setTransaction($transaction)->shouldBeCalled();
        $entityManager->persist($availability1)->shouldBeCalled();
        $entityManager->persist($availability2)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $messageFactory->create(1)->willReturn($transactionMessage);
        $messageBus->dispatch(
            $transactionMessage,
            [
                new DelayStamp(1000),
            ]
        )->shouldBeCalled()->willReturn(new Envelope(new \StdClass()));

        $this->__invoke($message);
    }
}

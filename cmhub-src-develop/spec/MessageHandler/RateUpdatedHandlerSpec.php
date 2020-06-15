<?php

namespace spec\App\MessageHandler;

use App\Entity\Factory\TransactionFactory;
use App\Entity\Partner;
use App\Entity\ProductRate;
use App\Entity\Transaction;
use App\Entity\TransactionChannel;
use App\Entity\TransactionStatus;
use App\Entity\TransactionType;
use App\Message\Factory\TransactionScheduledFactory;
use App\Message\RateUpdated;
use App\Message\TransactionScheduled;
use App\MessageHandler\RateUpdatedHandler;
use App\Repository\ProductRateRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class RateUpdatedHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RateUpdatedHandler::class);
    }

    function let(ProductRateRepository $rateRepository, EntityManagerInterface $entityManager, TransactionFactory $transactionFactory, MessageBusInterface $messageBus, TransactionScheduledFactory $messageFactory)
    {
        $this->beConstructedWith($rateRepository, $entityManager, $transactionFactory, $messageBus, $messageFactory);
    }

    function it_doesnt_process_message_if_entities_not_found(RateUpdated $message, ProductRateRepository $rateRepository)
    {
        $message->getRateIds()->willReturn([1,2]);
        $rateRepository->findBy(['id' => [1,2]])->willReturn([]);

        $this->shouldThrow(UnrecoverableMessageHandlingException::class)->during('__invoke', [$message]);
    }

    function it_process_message(RateUpdated $message, EntityManagerInterface $entityManager, ProductRateRepository $rateRepository,
                                ProductRate $rate1, ProductRate $rate2, Partner $partner, Transaction $transaction, TransactionFactory $transactionFactory,
                                MessageBusInterface $messageBus, TransactionScheduledFactory $messageFactory, TransactionScheduled $transactionMessage)
    {
        $message->getRateIds()->willReturn([1,2]);
        $rateRepository->findBy(['id' => [1,2]])->willReturn([$rate1, $rate2]);
        $rate1->getPartner()->willReturn($partner);
        $message->getChannel()->willReturn(TransactionChannel::IRESA);
        $transactionFactory->create(
            TransactionType::PRICE,
            TransactionChannel::IRESA,
            TransactionStatus::SCHEDULED,
            $partner
        )->willReturn($transaction);
        $transaction->getId()->willReturn(1);
        $entityManager->persist($transaction)->shouldBeCalled();
        $rate1->setTransaction($transaction)->shouldBeCalled()->willReturn($rate1);
        $rate2->setTransaction($transaction)->shouldBeCalled()->willReturn($rate2);
        $entityManager->persist($rate1)->shouldBeCalled();
        $entityManager->persist($rate2)->shouldBeCalled();

        $entityManager->flush()->shouldBeCalled();
        $messageFactory->create(1)->willReturn($transactionMessage);
        $messageBus->dispatch(
            $transactionMessage,
            [
                new DelayStamp(100),
            ]
        )->shouldBeCalled()->willReturn(new Envelope(new \StdClass()));

        $this->__invoke($message);
    }
}

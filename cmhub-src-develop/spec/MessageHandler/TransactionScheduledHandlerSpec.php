<?php

namespace spec\App\MessageHandler;

use App\Entity\Transaction;
use App\Exception\TransactionFailedException;
use App\Message\TransactionScheduled;
use App\MessageHandler\TransactionScheduledHandler;
use App\Repository\TransactionRepository;
use App\Service\Broadcaster\BroadcastManager;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

class TransactionScheduledHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TransactionScheduledHandler::class);
    }

    function let(BroadcastManager $broadcastManager, TransactionRepository $transactionRepository)
    {
        $this->beConstructedWith($broadcastManager, $transactionRepository);
    }

    function it_process_message_have_transaction_id(
        BroadcastManager $broadcastManager,
        TransactionRepository $transactionRepository,
        Transaction $transaction,
        TransactionScheduled $message
    ) {
        $message->getIdentifier()->willReturn('my_id');
        $transactionRepository->find('my_id')->willReturn($transaction);
        $broadcastManager->broadcast($transaction)->shouldBeCalled()->willReturn($transaction);
        $transaction->isFailed()->willReturn(false);

        $this->__invoke($message);
    }

    function it_process_message_no_transaction_id(
        BroadcastManager $broadcastManager,
        TransactionRepository $transactionRepository,
        Transaction $transaction,
        TransactionScheduled $message
    ) {
        $message->getIdentifier()->willReturn('my_id');
        $transactionRepository->find('my_id')->willReturn($transaction);
        $broadcastManager->broadcast($transaction)->shouldBeCalled()->willReturn($transaction);
        $transaction->isFailed()->willReturn(false);

        $this->__invoke($message);
    }

    function it_process_message_failed(
        BroadcastManager $broadcastManager,
        TransactionRepository $transactionRepository,
        Transaction $transaction,
        TransactionScheduled $message
    ) {
        $message->getIdentifier()->willReturn('my_id');
        $transactionRepository->find('my_id')->willReturn($transaction);
        $broadcastManager->broadcast($transaction)->shouldBeCalled()->willReturn($transaction);
        $transaction->isFailed()->willReturn(true);
        $transaction->getResponse()->willReturn('error');

        $this->shouldThrow(TransactionFailedException::class)->during('__invoke', [$message]);
    }

    function it_can_not_find_transaction(TransactionRepository $transactionRepository, TransactionScheduled $message)
    {
        $message->getIdentifier()->willReturn('my_id');
        $transactionRepository->find('my_id')->willReturn(null);
        $this->shouldThrow(UnrecoverableMessageHandlingException::class)->during('__invoke', [$message]);
    }
}

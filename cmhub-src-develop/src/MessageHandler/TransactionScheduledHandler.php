<?php

namespace App\MessageHandler;

use App\Entity\Transaction;
use App\Exception\TransactionFailedException;
use App\Message\TransactionScheduled;
use App\Repository\TransactionRepository;
use App\Service\Broadcaster\BroadcastManager;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class TransactionScheduledHandler
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class TransactionScheduledHandler implements MessageHandlerInterface
{
    /**
     * @var BroadcastManager
     */
    private $broadcastManager;

    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * TransactionScheduledHandler constructor.
     *
     * @param BroadcastManager      $broadcastManager
     * @param TransactionRepository $transactionRepository
     */
    public function __construct(BroadcastManager $broadcastManager, TransactionRepository $transactionRepository)
    {
        $this->broadcastManager = $broadcastManager;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     *
     * @param TransactionScheduled $message
     *
     * @return void
     *
     * @throws TransactionFailedException
     * @throws UnrecoverableMessageHandlingException
     */
    public function __invoke(TransactionScheduled $message)
    {
        /** @var Transaction $transaction */
        $transaction = $this->transactionRepository->find($message->getIdentifier());

        if (!$transaction) {
            throw new UnrecoverableMessageHandlingException(sprintf('Transaction with id `%s` has not been found in `%s`', $message->getIdentifier(), self::class));
        }

        $transaction = $this->broadcastManager->broadcast($transaction);

        if ($transaction->isFailed()) {
            throw new TransactionFailedException($transaction->getResponse());
        }
    }
}

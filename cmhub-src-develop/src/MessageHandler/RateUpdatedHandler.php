<?php

namespace App\MessageHandler;

use App\Entity\Factory\TransactionFactory;
use App\Entity\ProductRate;
use App\Entity\TransactionChannel;
use App\Entity\TransactionStatus;
use App\Entity\TransactionType;
use App\Message\Factory\TransactionScheduledFactory;
use App\Message\RateUpdated;
use App\Repository\ProductRateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * Class RateUpdatedHandler
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class RateUpdatedHandler implements MessageHandlerInterface
{
    /**
     * @var ProductRateRepository
     */
    private $rateRepository;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var TransactionScheduledFactory
     */
    private $messageFactory;

    /**
     * AvailabilityUpdatedHandler constructor.
     *
     * @param ProductRateRepository       $rateRepository
     * @param EntityManagerInterface      $entityManager
     * @param TransactionFactory          $transactionFactory
     * @param MessageBusInterface         $messageBus
     * @param TransactionScheduledFactory $messageFactory
     */
    public function __construct(ProductRateRepository $rateRepository, EntityManagerInterface $entityManager, TransactionFactory $transactionFactory, MessageBusInterface $messageBus, TransactionScheduledFactory $messageFactory)
    {
        $this->transactionFactory = $transactionFactory;
        $this->entityManager = $entityManager;
        $this->rateRepository = $rateRepository;
        $this->messageBus = $messageBus;
        $this->messageFactory = $messageFactory;
    }

    /**
     *
     * @param RateUpdated $message
     *
     * @return void
     */
    public function __invoke(RateUpdated $message)
    {
        $rates = $this->rateRepository->findBy(['id' => $message->getRateIds()]);
        if (!$rates) {
            throw new UnrecoverableMessageHandlingException(sprintf('Product Rate ids in the message are not present in the database.'));
        }

        $transaction = $this
            ->transactionFactory
            ->create(
                TransactionType::PRICE,
                $message->getChannel(),
                TransactionStatus::SCHEDULED,
                current($rates)->getPartner()
            );

        $this->entityManager->persist($transaction);

        /** @var ProductRate $rate */
        foreach ($rates as $rate) {
            $rate->setTransaction($transaction);
            $this->entityManager->persist($rate);
        }

        $this->entityManager->flush();

        $this
            ->messageBus
            ->dispatch(
                $this->messageFactory->create($transaction->getId()),
                [
                    new DelayStamp(100),
                    // Let time to mysql to add the transaction
                ]
            );
    }
}

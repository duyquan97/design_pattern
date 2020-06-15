<?php

namespace App\MessageHandler;

use App\Entity\Availability;
use App\Entity\Factory\TransactionFactory;
use App\Entity\TransactionStatus;
use App\Entity\TransactionType;
use App\Message\AvailabilityUpdated;
use App\Message\Factory\TransactionScheduledFactory;
use App\Repository\AvailabilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * Class AvailabilityUpdatedHandler
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailabilityUpdatedHandler implements MessageHandlerInterface
{
    /**
     * @var AvailabilityRepository
     */
    private $availabilityRepository;

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
     * @param AvailabilityRepository      $availabilityRepository
     * @param EntityManagerInterface      $entityManager
     * @param TransactionFactory          $transactionFactory
     * @param MessageBusInterface         $messageBus
     * @param TransactionScheduledFactory $messageFactory
     */
    public function __construct(AvailabilityRepository $availabilityRepository, EntityManagerInterface $entityManager, TransactionFactory $transactionFactory, MessageBusInterface $messageBus, TransactionScheduledFactory $messageFactory)
    {
        $this->transactionFactory = $transactionFactory;
        $this->entityManager = $entityManager;
        $this->availabilityRepository = $availabilityRepository;
        $this->messageBus = $messageBus;
        $this->messageFactory = $messageFactory;
    }

    /**
     *
     * @param AvailabilityUpdated $message
     *
     * @return void
     */
    public function __invoke(AvailabilityUpdated $message)
    {
        $availabilities = $this->availabilityRepository->findBy(['id' => $message->getAvailabilityIds()]);
        if (!$availabilities) {
            throw new UnrecoverableMessageHandlingException(sprintf('Availability ids in the message are not present in the database.'));
        }

        $transaction = $this
            ->transactionFactory
            ->create(
                TransactionType::AVAILABILITY,
                $message->getChannel(),
                TransactionStatus::SCHEDULED,
                current($availabilities)->getPartner()
            );

        $this->entityManager->persist($transaction);

        /** @var Availability $availability */
        foreach ($availabilities as $availability) {
            $availability->setTransaction($transaction);
            $this->entityManager->persist($availability);
        }

        $this->entityManager->flush();

        $this
            ->messageBus
            ->dispatch(
                $this->messageFactory->create($transaction->getId()),
                [
                    new DelayStamp(1000),
                    // Let time to mysql to add the transaction & to not send the transaction if a newer transaction has been received/processed.
                ]
            );
    }
}

<?php

namespace App\MessageHandler;

use App\Entity\Booking;
use App\Entity\Factory\TransactionFactory;
use App\Entity\TransactionStatus;
use App\Entity\TransactionType;
use App\Exception\TransactionFailedException;
use App\Message\SendBookingToChannel;
use App\Repository\BookingRepository;
use App\Service\Broadcaster\BroadcastManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class SendBookingToChannelHandler
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class SendBookingToChannelHandler implements MessageHandlerInterface
{
    /**
     * @var BroadcastManager
     */
    private $broadcastManager;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var BookingRepository
     */
    private $bookingRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * SendBookingToChannelHandler constructor.
     *
     * @param BroadcastManager       $broadcastManager
     * @param TransactionFactory     $transactionFactory
     * @param BookingRepository      $bookingRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(BroadcastManager $broadcastManager, TransactionFactory $transactionFactory, BookingRepository $bookingRepository, EntityManagerInterface $entityManager)
    {
        $this->broadcastManager = $broadcastManager;
        $this->transactionFactory = $transactionFactory;
        $this->bookingRepository = $bookingRepository;
        $this->entityManager = $entityManager;
    }

    /**
     *
     * @param SendBookingToChannel $message
     *
     * @return void
     *
     * @throws TransactionFailedException
     */
    public function __invoke(SendBookingToChannel $message)
    {
        /* @var Booking $booking */
        $booking = $this->bookingRepository->findOneBy(['identifier' => $message->getIdentifier()]);
        if (!$booking) {
            throw new UnrecoverableMessageHandlingException(sprintf('Booking with identifier `%s` has not been found in `%s`', $message->getIdentifier(), self::class));
        }

        if (!$transaction = $booking->getTransaction()) {
            $transaction = $this
                ->transactionFactory
                ->create(
                    TransactionType::BOOKING,
                    $booking->getChannelManager()->getIdentifier(),
                    TransactionStatus::SCHEDULED,
                    $booking->getPartner()
                );

            $booking->setTransaction($transaction);

            $this->entityManager->persist($booking);
            $this->entityManager->flush();
        }

        $transaction = $this->broadcastManager->broadcast($transaction);

        if ($transaction->isFailed()) {
            throw new TransactionFailedException($transaction->getResponse());
        }
    }
}

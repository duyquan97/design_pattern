<?php

namespace App\Service\Broadcaster;

use App\Entity\Booking;
use App\Entity\Transaction;
use App\Entity\TransactionChannel;
use App\Entity\TransactionStatus;
use App\Entity\TransactionType;
use App\Repository\BookingRepository;
use App\Exception\ChannelManagerClientException;
use App\Exception\ChannelManagerNotSupportedException;
use App\Exception\CmHubException;
use App\Exception\MissingTransactionDataException;
use App\Model\Factory\PushBookingFactory;
use App\Service\ChannelManager\ChannelManagerResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class IresaPushBookingsBroadcaster
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingBroadcaster implements BroadcasterInterface
{
    /**
     * @var PushBookingFactory
     */
    private $pushBookingFactory;

    /**
     * @var BookingRepository
     */
    private $bookingRepository;

    /**
     * @var ChannelManagerResolver
     */
    private $channelManagerResolver;

    /**
     * BookingBroadcaster constructor.
     *
     * @param PushBookingFactory $pushBookingFactory
     * @param BookingRepository $bookingRepository
     * @param ChannelManagerResolver $channelManagerResolver
     */
    public function __construct(PushBookingFactory $pushBookingFactory, BookingRepository $bookingRepository, ChannelManagerResolver $channelManagerResolver)
    {
        $this->pushBookingFactory = $pushBookingFactory;
        $this->channelManagerResolver = $channelManagerResolver;
        $this->bookingRepository = $bookingRepository;
    }

    /**
     * @return BookingRepository
     */
    public function getBookingRepository(): BookingRepository
    {
        return $this->bookingRepository;
    }

    /**
     *
     * @param Transaction $transaction
     *
     * @return Transaction
     *
     * @throws MissingTransactionDataException
     * @throws ChannelManagerNotSupportedException
     * @throws CmHubException
     */
    public function broadcast(Transaction $transaction): Transaction
    {
        /** @var Booking $booking */
        $booking = $this
            ->getBookingRepository()
            ->findOneBy(
                [
                    'transaction' => $transaction,
                ]
            );

        if (!$booking) {
            throw new MissingTransactionDataException($transaction);
        }

        $pushBooking = $this
            ->pushBookingFactory
            ->create()
            ->setBooking($booking);

        $channelManager = $booking->getPartner()->getChannelManager();

        if ($channelManager && $channelManager->isPushBookings()) {
            $this
                ->channelManagerResolver
                ->getIntegration($channelManager)
                ->pushBookingRequest($pushBooking)
            ;
        }

        return $transaction;
    }

    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function support(Transaction $transaction): bool
    {
        return TransactionType::BOOKING === $transaction->getType();
    }
}

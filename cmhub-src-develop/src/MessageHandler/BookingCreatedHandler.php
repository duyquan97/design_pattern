<?php

namespace App\MessageHandler;

use App\Entity\Booking;
use App\Message\BookingCreated;
use App\Message\Factory\SendBookingToChannelFactory;
use App\Repository\BookingRepository;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * Class BookingCreatedHandler
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingCreatedHandler implements MessageHandlerInterface
{
    /**
     * @var BookingRepository
     */
    private $bookingRepository;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var SendBookingToChannelFactory
     */
    private $messageFactory;

    /**
     * BookingCreatedHandler constructor.
     *
     * @param BookingRepository           $bookingRepository
     * @param MessageBusInterface         $messageBus
     * @param SendBookingToChannelFactory $messageFactory
     */
    public function __construct(BookingRepository $bookingRepository, MessageBusInterface $messageBus, SendBookingToChannelFactory $messageFactory)
    {
        $this->bookingRepository = $bookingRepository;
        $this->messageBus = $messageBus;
        $this->messageFactory = $messageFactory;
    }

    /**
     *
     * @param BookingCreated $message
     *
     * @return void
     */
    public function __invoke(BookingCreated $message)
    {
        /* @var Booking $booking */
        $booking = $this->bookingRepository->findOneBy(['identifier' => $message->getIdentifier()]);
        if (!$booking) {
            throw new UnrecoverableMessageHandlingException(sprintf('Booking with identifier `%s` has not been found in `%s`', $message->getIdentifier(), self::class));
        }

        if ($booking->getPartner()->isEnabled() && $booking->getPartner()->getChannelManager()->isPushBookings()) {
            $this
                ->messageBus
                ->dispatch(
                    $this->messageFactory->create($booking->getReservationId()),
                    [
                        new DelayStamp(500),
                    ]
                );
        }
    }
}

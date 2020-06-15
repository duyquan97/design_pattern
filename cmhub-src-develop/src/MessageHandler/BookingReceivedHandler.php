<?php

namespace App\MessageHandler;

use App\Booking\BookingManager;
use App\Exception\BookingAlreadyProcessedException;
use App\Message\BookingReceived;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class BookingReceivedHandler
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingReceivedHandler implements MessageHandlerInterface
{
    /**
     * @var BookingManager
     */
    protected $bookingManager;

    /**
     * BookingReceivedHandler constructor.
     *
     * @param BookingManager $bookingManager
     */
    public function __construct(BookingManager $bookingManager)
    {
        $this->bookingManager = $bookingManager;
    }

    /**
     *
     * @param BookingReceived $message
     *
     * @return void
     */
    public function __invoke(BookingReceived $message)
    {
        $processBooking = $message->getBooking();

        try {
            $this->bookingManager->create($processBooking);
        } catch (BookingAlreadyProcessedException $exception) {
            throw new UnrecoverableMessageHandlingException($exception->getMessage());
        }
    }
}

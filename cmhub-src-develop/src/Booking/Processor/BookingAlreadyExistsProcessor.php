<?php

namespace App\Booking\Processor;

use App\Exception\BookingAlreadyProcessedException;
use App\Model\BookingInterface;
use App\Repository\BookingRepository;
use App\Booking\BookingProcessorInterface;

/**
 * Class BookingAlreadyExistsProcessor
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingAlreadyExistsProcessor implements BookingProcessorInterface
{
    /**
     * @var BookingRepository
     */
    private $bookingRepository;

    /**
     * BookingAlreadyExistsProcessor constructor.
     *
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    /**
     *
     * @param BookingInterface $booking
     *
     * @return BookingInterface
     *
     * @throws BookingAlreadyProcessedException
     */
    public function process(BookingInterface $booking): BookingInterface
    {
        $existingBooking = $this->bookingRepository->findOneByIdentifier($booking->getReservationId());
        if (!$existingBooking) {
            return $booking;
        }

        if ($existingBooking->isProcessed() && $booking->getStatus() === $existingBooking->getStatus()) {
            throw new BookingAlreadyProcessedException($booking);
        }

        return $booking;
    }
}

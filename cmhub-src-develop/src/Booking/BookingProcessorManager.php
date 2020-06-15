<?php

namespace App\Booking;

use App\Exception\BookingAlreadyProcessedException;
use App\Model\BookingInterface;

/**
 * Class BookingProcessorManager
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingProcessorManager
{
    /**
     * @var BookingProcessorInterface[]
     */
    private $processors;

    /**
     * BookingProcessorManager constructor.
     *
     * @param BookingProcessorInterface[] $processors
     */
    public function __construct(array $processors)
    {
        $this->processors = $processors;
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
        foreach ($this->processors as $processor) {
            $booking = $processor->process($booking);
        }

        return $booking;
    }
}

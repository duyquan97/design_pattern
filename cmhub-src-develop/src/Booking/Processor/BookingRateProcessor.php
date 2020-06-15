<?php

namespace App\Booking\Processor;

use App\Entity\Factory\BookingProductRateFactory;
use App\Model\BookingInterface;
use App\Booking\BookingProcessorInterface;

/**
 * Class BookingRateProcessor
 *
 * This processor is adding missing rates of a booking received from iResa.
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingRateProcessor implements BookingProcessorInterface
{
    /**
     * @var BookingProductRateFactory
     */
    private $bookingProductRateFactory;

    /**
     * BookingAmountProcessor constructor.
     *
     * @param BookingProductRateFactory $bookingProductRateFactory
     */
    public function __construct(BookingProductRateFactory $bookingProductRateFactory)
    {
        $this->bookingProductRateFactory = $bookingProductRateFactory;
    }

    /**
     *
     * @param BookingInterface $booking
     *
     * @return BookingInterface
     */
    public function process(BookingInterface $booking): BookingInterface
    {
        foreach ($booking->getBookingProducts() as $bookingProduct) {
            $date = clone $booking->getStartDate();
            while ($date < $booking->getEndDate()) {
                if (!$bookingProduct->hasRate($date)) {
                    $rate = $this
                        ->bookingProductRateFactory
                        ->create(
                            $bookingProduct,
                            $date,
                            0,
                            $booking->getPartner()->getCurrency()
                        );

                    $bookingProduct->addRate($rate);
                }

                $date->modify('+1 day');
            }
        }

        return $booking;
    }
}

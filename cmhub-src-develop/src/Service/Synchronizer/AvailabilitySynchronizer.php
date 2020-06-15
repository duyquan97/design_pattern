<?php

namespace App\Service\Synchronizer;

use App\Entity\TransactionChannel;
use App\Model\PartnerInterface;
use App\Model\ProductAvailabilityCollection;
use App\Service\BookingEngineManager;
use App\Service\Synchronizer\Diff\AvailabilityDiff;

/**
 * Class AvailabilitySynchronizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailabilitySynchronizer implements SynchronizerInterface
{
    public const TYPE = 'availability';

    /**
     * @var AvailabilityDiff
     */
    private $availabilityDiff;

    /**
     * @var BookingEngineManager
     */
    private $bookingEngine;

    /**
     * AvailabilitySynchronizer constructor.
     *
     * @param AvailabilityDiff     $availabilityDiff
     * @param BookingEngineManager $bookingEngine
     */
    public function __construct(AvailabilityDiff $availabilityDiff, BookingEngineManager $bookingEngine)
    {
        $this->availabilityDiff = $availabilityDiff;
        $this->bookingEngine = $bookingEngine;
    }

    /**
     *
     * @param PartnerInterface $partner
     * @param \DateTime        $start
     * @param \DateTime        $end
     *
     * @return ProductAvailabilityCollection[]|array
     *
     * @throws \Exception
     */
    public function sync(PartnerInterface $partner, \DateTime $start, \DateTime $end): array
    {
        $collections = [];
        $startDate = clone $start;
        $endDate = (clone $start)->modify('+1 month');

        while ($startDate <= $end) {
            $productAvailabilityCollection = $this->availabilityDiff->diff($partner, $startDate, $endDate);
            $startDate->modify('+1 month');
            $endDate->modify('+1 month');
            if ($productAvailabilityCollection->isEmpty()) {
                continue;
            }

            $collections[] = $this->bookingEngine->updateAvailability($productAvailabilityCollection->setChannel(TransactionChannel::IRESA));
        }

        return $collections;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function support(string $type): bool
    {
        return self::TYPE === $type;
    }
}

<?php

namespace App\Service\Synchronizer;

use App\Entity\TransactionChannel;
use App\Model\AvailabilitySource;
use App\Model\PartnerInterface;
use App\Repository\AvailabilityRepository;
use App\Service\BookingEngineManager;

/**
 * Class AvailabilityForcedAlignment
 *
 * Forced alignment of all the availability data without finding differences.
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailabilityForcedAlignment implements SynchronizerInterface
{
    public const TYPE = 'availability_forced';

    /**
     * @var BookingEngineManager
     */
    private $bookingEngine;

    /**
     * @var AvailabilityRepository
     */
    private $availabilityRepository;

    /**
     * AvailabilityForcedAlignment constructor.
     *
     * @param BookingEngineManager $bookingEngine
     * @param AvailabilityRepository $availabilityRepository
     */
    public function __construct(BookingEngineManager $bookingEngine, AvailabilityRepository $availabilityRepository)
    {
        $this->bookingEngine = $bookingEngine;
        $this->availabilityRepository = $availabilityRepository;
    }

    /**
     *
     * @param PartnerInterface $partner
     * @param \DateTime        $start
     * @param \DateTime        $end
     *
     * @return array
     */
    public function sync(PartnerInterface $partner, \DateTime $start, \DateTime $end)
    {
        $collections = [];
        $startDate = clone $start;
        $endDate = (clone $start)->modify('+1 month');

        foreach ($partner->getProducts() as $product) {
            if (!$product->isMaster()) {
                 $this->availabilityRepository->reset($product);
            }
        }

        while ($startDate <= $end) {
            $productAvailabilityCollection = $this->bookingEngine->getAvailabilities($partner, $startDate, $endDate);
            $startDate->modify('+1 month');
            $endDate->modify('+1 month');
            if ($productAvailabilityCollection->isEmpty()) {
                continue;
            }

            $productAvailabilityCollection->setChannel(TransactionChannel::IRESA)->setSource(AvailabilitySource::ALIGNMENT);
            $this->bookingEngine->updateAvailability($productAvailabilityCollection);
            $this->availabilityRepository->clear();
        }

        return $collections;
    }

    /**
     *
     * @param string $type
     *
     * @return bool
     */
    public function support(string $type): bool
    {
        return static::TYPE === $type;
    }
}

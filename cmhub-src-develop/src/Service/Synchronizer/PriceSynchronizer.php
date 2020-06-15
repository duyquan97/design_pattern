<?php

namespace App\Service\Synchronizer;

use App\Model\PartnerInterface;
use App\Model\ProductAvailabilityCollection;
use App\Service\BookingEngineManager;
use App\Service\Synchronizer\Diff\PriceDiff;

/**
 * Class PriceSynchronizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PriceSynchronizer implements SynchronizerInterface
{
    public const TYPE = 'price';

    /**
     * @var BookingEngineManager
     */
    private $bookingEngine;

    /**
     * @var PriceDiff
     */
    private $rateDiff;

    /**
     * PriceSynchronizer constructor.
     *
     * @param BookingEngineManager $bookingEngine
     * @param PriceDiff            $rateDiff
     */
    public function __construct(BookingEngineManager $bookingEngine, PriceDiff $rateDiff)
    {
        $this->bookingEngine = $bookingEngine;
        $this->rateDiff = $rateDiff;
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
    public function sync(PartnerInterface $partner, \DateTime $start, \DateTime $end)
    {
        $collections = [];
        while ($start <= $end) {
            $startDate = clone $start;
            $start->modify('+1 month');

            $productRateCollection = $this->rateDiff->diff($partner, $startDate, $start);
            if ($productRateCollection->isEmpty()) {
                continue;
            }

            $productRateCollection = $this->bookingEngine->updateRates($productRateCollection);

            $collections[] = $productRateCollection;
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

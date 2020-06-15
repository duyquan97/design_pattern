<?php

namespace App\Service\Synchronizer;

use App\Entity\TransactionChannel;
use App\Model\PartnerInterface;
use App\Repository\ProductRateRepository;
use App\Service\BookingEngineManager;

/**
 * Class PriceForcedAlignment
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PriceForcedAlignment implements SynchronizerInterface
{
    public const TYPE = 'price_forced';

    /**
     * @var BookingEngineManager
     */
    private $bookingEngine;

    /**
     * @var ProductRateRepository
     */
    private $rateRepository;

    /**
     * AvailabilityForcedAlignment constructor.
     *
     * @param BookingEngineManager $bookingEngine
     * @param ProductRateRepository $rateRepository
     */
    public function __construct(BookingEngineManager $bookingEngine, ProductRateRepository $rateRepository)
    {
        $this->bookingEngine = $bookingEngine;
        $this->rateRepository = $rateRepository;
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
                $this->rateRepository->reset($product);
            }
        }

        while ($startDate <= $end) {
            $productRateCollection = $this->bookingEngine->getRates($partner, $startDate, $endDate);
            $startDate->modify('+1 month');
            $endDate->modify('+1 month');
            if ($productRateCollection->isEmpty()) {
                continue;
            }

            $this->bookingEngine->updateRates($productRateCollection->setChannel(TransactionChannel::IRESA));
            $this->rateRepository->clear();
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

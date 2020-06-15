<?php

namespace App\Service;

use App\Entity\ChannelManager;
use App\Model\BookingCollection;
use App\Model\PartnerInterface;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Model\ProductInterface;
use App\Model\ProductRateCollection;
use App\Model\ProductRateCollectionInterface;

/**
 * Interface BookingEngineInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface BookingEngineInterface
{
    /**
     *
     * @param ProductAvailabilityCollectionInterface $productAvailabilities
     *
     * @return ProductAvailabilityCollectionInterface
     */
    public function updateAvailability(ProductAvailabilityCollectionInterface $productAvailabilities): ProductAvailabilityCollectionInterface;

    /**
     *
     * @param PartnerInterface   $partner
     * @param \DateTime          $start
     * @param \DateTime          $end
     * @param ProductInterface[] $products
     *
     * @return ProductAvailabilityCollectionInterface
     */
    public function getAvailabilities(PartnerInterface $partner, \DateTime $start, \DateTime $end, array $products = array()): ProductAvailabilityCollectionInterface;

    /**
     *
     * @param \DateTime $start
     * @param \DateTime $end
     * @param ChannelManager|null $channelManager
     * @param array $partners
     * @param string $dateType
     *
     * @return BookingCollection The bookings retrieved
     */
    public function getBookings(\DateTime $start, \DateTime $end, ChannelManager $channelManager = null, array $partners = array(), string $dateType = null): BookingCollection;

    /**
     *
     * @param ProductRateCollectionInterface $productRateCollection
     *
     * @return ProductRateCollectionInterface The updated rates
     */
    public function updateRates(ProductRateCollectionInterface $productRateCollection): ProductRateCollectionInterface;

    /**
     *
     * @param PartnerInterface        $partner
     * @param \DateTime               $start
     * @param \DateTime               $end
     * @param ProductInterface[] $products
     *
     * @return ProductRateCollection
     */
    public function getRates(PartnerInterface $partner, \DateTime $start, \DateTime $end, array $products = array()): ProductRateCollection;
}

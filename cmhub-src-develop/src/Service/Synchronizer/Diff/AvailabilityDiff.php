<?php

namespace App\Service\Synchronizer\Diff;

use App\Exception\CmHubException;
use App\Model\AvailabilitySource;
use App\Model\Factory\ProductAvailabilityCollectionFactory;
use App\Model\Factory\ProductAvailabilityFactory;
use App\Model\PartnerInterface;
use App\Model\ProductAvailabilityCollection;
use App\Service\BookingEngineManager;
use App\Service\Iresa\IresaBookingEngine;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class AvailabilityDiff
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailabilityDiff
{
    /**
     * @var IresaBookingEngine
     */
    private $target;

    /**
     * @var BookingEngineManager
     */
    private $source;

    /**
     * @var ProductAvailabilityFactory
     */
    private $productAvailabilityFactory;

    /**
     * @var ProductAvailabilityCollectionFactory
     */
    private $productAvailabilityCollectionFactory;

    /**
     * @var ProductLoader
     */
    private $productLoader;

    /**
     * @var PartnerLoader
     */
    private $partnerLoader;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * AvailabilityDiff constructor.
     *
     * @param IresaBookingEngine                   $iresaBookingEngine
     * @param BookingEngineManager                 $bookingEngine
     * @param ProductAvailabilityFactory           $productAvailabilityFactory
     * @param ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory
     * @param ProductLoader                        $productLoader
     * @param PartnerLoader                        $partnerLoader
     * @param EntityManagerInterface               $entityManager
     */
    public function __construct(IresaBookingEngine $iresaBookingEngine, BookingEngineManager $bookingEngine, ProductAvailabilityFactory $productAvailabilityFactory, ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory, ProductLoader $productLoader, PartnerLoader $partnerLoader, EntityManagerInterface $entityManager)
    {
        $this->target = $iresaBookingEngine;
        $this->source = $bookingEngine;
        $this->productAvailabilityFactory = $productAvailabilityFactory;
        $this->productAvailabilityCollectionFactory = $productAvailabilityCollectionFactory;
        $this->productLoader = $productLoader;
        $this->partnerLoader = $partnerLoader;
        $this->entityManager = $entityManager;
    }

    /**
     *
     * @param PartnerInterface $partner
     * @param \DateTime        $start
     * @param \DateTime        $end
     *
     * @return ProductAvailabilityCollection
     *
     * @throws CmHubException
     */
    public function diff(PartnerInterface $partner, \DateTime $start, \DateTime $end)
    {
        $productAvailabilityCollection = $this->productAvailabilityCollectionFactory->create($partner);
        $productAvailabilityCollection->setSource(AvailabilitySource::ALIGNMENT);

        $products = $this->productLoader->getByPartner($partner);
        $cmhubCollection = $this->source->getAvailabilities($partner, $start, $end, $products->toArray());
        $iresaCollection = $this->target->getAvailabilities($partner, $start, $end, $products->toArray());

        foreach ($cmhubCollection->getProductAvailabilities() as $productAvailability) {
            $product = $productAvailability->getProduct();
            if (!$product->isMaster()) {
                continue;
            }

            $availabilityDiff = $this->productAvailabilityFactory->create($product);
            foreach ($productAvailability->getAvailabilities() as $availability) {
                $iresaAvailability = $iresaCollection->getByProductAndDate($product, $availability->getStart());
                if (!$iresaAvailability) {
                    $availabilityDiff->addAvailability($availability);
                    continue;
                }

                if ($iresaAvailability->getStock() === 0 && $availability->isStopSale()) {
                    continue;
                }

                if ($iresaAvailability->getStock() === $availability->getStock()) {
                    continue;
                }

                $availabilityDiff->addAvailability($availability);
            }

            if (!$availabilityDiff->isEmpty()) {
                $productAvailabilityCollection->addProductAvailability($availabilityDiff);
            }
        }

        return $productAvailabilityCollection;
    }
}

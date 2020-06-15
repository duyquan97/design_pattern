<?php

namespace App\Service\Synchronizer\Diff;

use App\Exception\CmHubException;
use App\Model\Factory\ProductRateCollectionFactory;
use App\Model\Factory\ProductRateFactory;
use App\Model\PartnerInterface;
use App\Model\ProductRate;
use App\Model\ProductRateCollection;
use App\Service\HubEngine\CmHubBookingEngine;
use App\Service\Iresa\IresaBookingEngine;
use App\Service\Loader\ProductLoader;

/**
 * Class RateDiff
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PriceDiff
{
    /**
     * @var IresaBookingEngine
     */
    private $iresaBookingEngine;

    /**
     * @var CmHubBookingEngine
     */
    private $cmhubBookingEngine;

    /**
     * @var ProductRateFactory
     */
    private $productRateFactory;

    /**
     * @var ProductRateCollectionFactory
     */
    private $productRateCollectionFactory;

    /**
     * @var ProductLoader
     */
    private $productLoader;

    /**
     * PriceDiff constructor.
     *
     * @param IresaBookingEngine           $iresaBookingEngine
     * @param CmHubBookingEngine           $cmhubBookingEngine
     * @param ProductRateFactory           $productRateFactory
     * @param ProductRateCollectionFactory $productRateCollectionFactory
     * @param ProductLoader                $productLoader
     */
    public function __construct(IresaBookingEngine $iresaBookingEngine, CmHubBookingEngine $cmhubBookingEngine, ProductRateFactory $productRateFactory, ProductRateCollectionFactory $productRateCollectionFactory, ProductLoader $productLoader)
    {
        $this->iresaBookingEngine = $iresaBookingEngine;
        $this->cmhubBookingEngine = $cmhubBookingEngine;
        $this->productRateFactory = $productRateFactory;
        $this->productRateCollectionFactory = $productRateCollectionFactory;
        $this->productLoader = $productLoader;
    }

    /**
     *
     * @param PartnerInterface $partner
     * @param \DateTime        $start
     * @param \DateTime        $end
     *
     * @return ProductRateCollection
     *
     * @throws CmHubException
     */
    public function diff(PartnerInterface $partner, \DateTime $start, \DateTime $end)
    {
        $productRateCollection = $this->productRateCollectionFactory->create($partner);
        $products = $this->productLoader->getByPartner($partner);
        $cmhubCollection = $this->cmhubBookingEngine->getRates($partner, $start, $end, $products->toArray());
        $iresaCollection = $this->iresaBookingEngine->getRates($partner, $start, $end, $products->toArray());

        /** @var ProductRate $productRate */
        foreach ($cmhubCollection->getProductRates() as $productRate) {
            $product = $productRate->getProduct();
            if (!$product->isMaster()) {
                continue;
            }

            $priceDiff = $this->productRateFactory->create($product);
            foreach ($productRate->getRates() as $rate) {
                $iresaRate = $iresaCollection->getByProductAndDate($product, $rate->getStart());
                if (!$iresaRate) {
                    $priceDiff->addRate($rate);
                    continue;
                }

                if ($iresaRate->getAmount() === $rate->getAmount()) {
                    continue;
                }

                $priceDiff->addRate($rate);
            }

            if (!$priceDiff->isEmpty()) {
                $productRateCollection->addProductRate($priceDiff);
            }
        }

        return $productRateCollection;
    }
}

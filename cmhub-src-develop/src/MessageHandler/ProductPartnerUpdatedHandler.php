<?php

namespace App\MessageHandler;

use App\Message\ProductPartnerUpdated;
use App\Repository\AvailabilityRepository;
use App\Repository\ProductRateRepository;
use App\Service\Loader\ProductLoader;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class ProductPartnerUpdatedHandler
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductPartnerUpdatedHandler implements MessageHandlerInterface
{
    /**
     * @var ProductRateRepository
     */
    private $productRateRepository;

    /**
     * @var AvailabilityRepository
     */
    private $availabilityRepository;

    /**
     * @var ProductLoader
     */
    private $productLoader;

    /**
     * ProductPartnerUpdatedHandler constructor.
     *
     * @param ProductRateRepository  $productRateRepository
     * @param AvailabilityRepository $availabilityRepository
     * @param ProductLoader          $productLoader
     */
    public function __construct(ProductRateRepository $productRateRepository, AvailabilityRepository $availabilityRepository, ProductLoader $productLoader)
    {
        $this->productRateRepository = $productRateRepository;
        $this->availabilityRepository = $availabilityRepository;
        $this->productLoader = $productLoader;
    }

    /**
     *
     * @param ProductPartnerUpdated $message
     *
     * @return void
     *
     */
    public function __invoke(ProductPartnerUpdated $message)
    {
        $product = $this->productLoader->getProductByIdentifier($message->getIdentifier());
        $this->productRateRepository->updatePartner($product);
        $this->availabilityRepository->updatePartner($product);
    }
}

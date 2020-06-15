<?php

namespace App\MessageHandler;

use App\Message\Factory\SyncDataFactory;
use App\Message\MasterProductUpdated;
use App\Repository\AvailabilityRepository;
use App\Repository\ProductRateRepository;
use App\Service\Loader\ProductLoader;
use App\Service\Synchronizer\AvailabilityForcedAlignment;
use App\Service\Synchronizer\AvailabilitySynchronizer;
use App\Service\Synchronizer\PriceForcedAlignment;
use App\Service\Synchronizer\PriceSynchronizer;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class MasterProductUpdatedHandler
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class MasterProductUpdatedHandler implements MessageHandlerInterface
{
    /**
     * @var ProductLoader
     */
    private $productLoader;

    /**
     * @var AvailabilityRepository
     */
    private $availabilityRepository;

    /**
     * @var ProductRateRepository
     */
    private $productRateRepository;

    /**
     * @var SyncDataFactory
     */
    private $messageFactory;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * MasterProductUpdatedHandler constructor.
     *
     * @param ProductLoader          $productLoader
     * @param AvailabilityRepository $availabilityRepository
     * @param ProductRateRepository  $productRateRepository
     * @param SyncDataFactory        $messageFactory
     * @param MessageBusInterface    $messageBus
     */
    public function __construct(ProductLoader $productLoader, AvailabilityRepository $availabilityRepository, ProductRateRepository $productRateRepository, SyncDataFactory $messageFactory, MessageBusInterface $messageBus)
    {
        $this->productLoader = $productLoader;
        $this->availabilityRepository = $availabilityRepository;
        $this->productRateRepository = $productRateRepository;
        $this->messageFactory = $messageFactory;
        $this->messageBus = $messageBus;
    }

    /**
     *
     * @param MasterProductUpdated $message
     *
     * @return void
     */
    public function __invoke(MasterProductUpdated $message)
    {
        $product = $this->productLoader->getProductByIdentifier($message->getIdentifier());
        if (!$product) {
            throw new UnrecoverableMessageHandlingException(sprintf('Product identifier `%s` has not been found in DB', $message->getIdentifier()));
        }

        if ($product->isMaster()) {
            // Product became master
            $this->availabilityRepository->reset($product);
            $this->productRateRepository->reset($product);
        }

        if ($product->getPartner()->isEnabled()) {
            $this->messageBus->dispatch($this->messageFactory->create($product->getPartner()->getIdentifier(), AvailabilityForcedAlignment::TYPE));
            $this->messageBus->dispatch($this->messageFactory->create($product->getPartner()->getIdentifier(), PriceForcedAlignment::TYPE));
        }
    }
}

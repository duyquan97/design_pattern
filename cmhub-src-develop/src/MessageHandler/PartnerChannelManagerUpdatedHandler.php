<?php

namespace App\MessageHandler;

use App\Message\Factory\SyncDataFactory;
use App\Message\PartnerChannelManagerUpdated;
use App\Repository\AvailabilityRepository;
use App\Repository\ProductRateRepository;
use App\Service\Loader\PartnerLoader;
use App\Service\Synchronizer\AvailabilityForcedAlignment;
use App\Service\Synchronizer\PriceForcedAlignment;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class PartnerChannelManagerUpdatedHandler
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PartnerChannelManagerUpdatedHandler implements MessageHandlerInterface
{
    /**
     * @var AvailabilityRepository
     */
    private $availabilityRepository;

    /**
     * @var ProductRateRepository
     */
    private $productRateRepository;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var PartnerLoader
     */
    private $partnerLoader;

    /**
     * @var SyncDataFactory
     */
    private $syncDataFactory;

    /**
     * PartnerChannelManagerUpdatedHandler constructor.
     *
     * @param AvailabilityRepository $availabilityRepository
     * @param ProductRateRepository  $productRateRepository
     * @param MessageBusInterface    $messageBus
     * @param PartnerLoader          $partnerLoader
     * @param SyncDataFactory        $syncDataFactory
     */
    public function __construct(AvailabilityRepository $availabilityRepository, ProductRateRepository $productRateRepository, MessageBusInterface $messageBus, PartnerLoader $partnerLoader, SyncDataFactory $syncDataFactory)
    {
        $this->availabilityRepository = $availabilityRepository;
        $this->productRateRepository = $productRateRepository;
        $this->messageBus = $messageBus;
        $this->partnerLoader = $partnerLoader;
        $this->syncDataFactory = $syncDataFactory;
    }

    /**
     *
     * @param PartnerChannelManagerUpdated $message
     *
     * @return void
     */
    public function __invoke(PartnerChannelManagerUpdated $message)
    {
        $partner = $this->partnerLoader->find($message->getIdentifier());
        if (!$partner) {
            throw new UnrecoverableMessageHandlingException(sprintf('Partner id in the message are not present in the database.'));
        }

        $products = $partner->getProducts();
        foreach ($products as $product) {
            $this->availabilityRepository->reset($product);
            $this->productRateRepository->reset($product);
        }

        $this->messageBus->dispatch($this->syncDataFactory->create($message->getIdentifier(), AvailabilityForcedAlignment::TYPE));
        $this->messageBus->dispatch($this->syncDataFactory->create($message->getIdentifier(), PriceForcedAlignment::TYPE));
    }
}

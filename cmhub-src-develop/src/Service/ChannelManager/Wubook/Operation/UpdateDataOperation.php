<?php

namespace App\Service\ChannelManager\Wubook\Operation;

use App\Entity\Partner;
use App\Exception\NormalizerNotFoundException;
use App\Exception\ValidationException;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductRateCollection;
use App\Model\ProductRateCollectionInterface;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\Wubook\Serializer\WubookSerializer;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;

/**
 * Class UpdateDataOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class UpdateDataOperation implements WubookOperationInterface
{
    public const NAME = 'update_data';

    /**
     * @var BookingEngineInterface
     */
    private $bookingEngine;

    /**
     * @var WubookSerializer
     */
    private $wubookSerializer;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     * UpdateDataOperation constructor.
     *
     * @param BookingEngineInterface $bookingEngine
     * @param WubookSerializer       $wubookSerializer
     * @param CmhubLogger            $logger
     */
    public function __construct(BookingEngineInterface $bookingEngine, WubookSerializer $wubookSerializer, CmhubLogger $logger)
    {
        $this->bookingEngine = $bookingEngine;
        $this->wubookSerializer = $wubookSerializer;
        $this->logger = $logger;
    }

    /**
     *
     * @param \stdClass $request
     * @param Partner $partner
     *
     * @return array
     *
     * @throws NormalizerNotFoundException
     * @throws ValidationException
     */
    public function handle(\stdClass $request, Partner $partner): array
    {
        if (!isset($request->data)) {
            throw new ValidationException('"data" is mandatory');
        }

        /** @var ProductRateCollectionInterface $productRateCollection */
        $productRateCollection = $this->wubookSerializer->denormalize($request->data, ProductRateCollection::class, ['partner' => $partner]);

        /** @var ProductAvailabilityCollection $productAvailabilityCollection */
        $productAvailabilityCollection = $this->wubookSerializer->denormalize($request->data, ProductAvailabilityCollection::class, ['partner' => $partner]);

        $this->bookingEngine->updateRates($productRateCollection);
        $this->bookingEngine->updateAvailability($productAvailabilityCollection);

        $this->logger->addOperationInfo(
            LogAction::UPDATE_DATA,
            $partner,
            $this
        );

        return [];
    }

    /**
     *
     * @param string $operation
     *
     * @return bool
     */
    public function supports(string $operation): bool
    {
        return static::NAME === $operation;
    }
}

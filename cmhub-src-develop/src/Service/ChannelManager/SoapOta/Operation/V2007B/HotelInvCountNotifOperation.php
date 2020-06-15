<?php

namespace App\Service\ChannelManager\SoapOta\Operation\V2007B;

use App\Exception\AccessDeniedException;
use App\Exception\DateFormatException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\Availability;
use App\Model\ProductAvailabilityCollection;
use App\Security\Voter\PartnerVoter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\SoapOta\SoapOtaOperationInterface;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use App\Utils\Util;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class HotelInvCountNotifOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class HotelInvCountNotifOperation implements SoapOtaOperationInterface
{
    public const OPERATION_NAME = 'OTA_HotelInvCountNotifRQ';

    /**
     *
     * @var BookingEngineInterface
     */
    private $bookingEngine;

    /**
     *
     * @var PartnerLoader
     */
    private $partnerLoader;

    /**
     *
     * @var ProductLoader
     */
    private $productLoader;

    /**
     *
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     * HotelInvCountNotifOperation constructor.
     *
     * @param BookingEngineInterface        $bookingEngine
     * @param PartnerLoader                 $partnerLoader
     * @param ProductLoader                 $productLoader
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param CmhubLogger                   $logger
     */
    public function __construct(BookingEngineInterface $bookingEngine, PartnerLoader $partnerLoader, ProductLoader $productLoader, AuthorizationCheckerInterface $authorizationChecker, CmhubLogger $logger)
    {
        $this->bookingEngine = $bookingEngine;
        $this->partnerLoader = $partnerLoader;
        $this->productLoader = $productLoader;
        $this->authorizationChecker = $authorizationChecker;
        $this->logger = $logger;
    }

    /**
     *
     * @param \StdClass $request
     *
     * @return array
     *
     * @throws AccessDeniedException
     * @throws DateFormatException
     * @throws ProductNotFoundException
     * @throws ValidationException
     */
    public function handle(\StdClass $request): array
    {
        $partner = $this->partnerLoader->find($hotelCode = $request->Inventories->HotelCode);
        if (!$this->authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)) {
            throw new AccessDeniedException(Response::HTTP_FORBIDDEN, $hotelCode);
        }

        $productAvailabilityCollection = new ProductAvailabilityCollection($partner);
        $inventories = Util::toArray($request->Inventories->Inventory);

        foreach ($inventories as $inventory) {
            if (!$product = $this->productLoader->find($partner, $inventory->StatusApplicationControl->InvTypeCode)) {
                $this->logger->addOperationException('', new ProductNotFoundException($partner, $inventory->StatusApplicationControl->InvTypeCode), $this);
                continue;
            }

            $startDate = \DateTime::createFromFormat('Y-m-d', $inventory->StatusApplicationControl->Start);
            $endDate = \DateTime::createFromFormat('Y-m-d', $inventory->StatusApplicationControl->End);
            if ($startDate > $endDate) {
                throw new ValidationException('Start date cannot be greater than end date');
            }
            if (!$startDate || !$endDate) {
                throw new DateFormatException('Y-m-d');
            }

            if ($inventory->InvCounts->InvCount->Count < 0 || $inventory->InvCounts->InvCount->Count > 9999) {
                throw new ValidationException('The value of the stock cannot be less than 0 nor more than 9999');
            }

            $productAvailabilityCollection->addEnabledWeekDays($inventory->StatusApplicationControl);

            $availability = new Availability($product);
            $availability
                ->setStart($startDate)
                ->setEnd($endDate)
                ->setStock($inventory->InvCounts->InvCount->Count);

            $productAvailabilityCollection->addAvailability($availability);
            $productAvailabilityCollection->setEnabledWeekDays([]);
        }

        $this->bookingEngine->updateAvailability($productAvailabilityCollection);

        $this->logger->addOperationInfo(LogAction::UPDATE_AVAILABILITY, $partner, $this);

        return [];
    }

    /**
     * @param string $operation The operation
     *
     * @return boolean
     */
    public function supports(string $operation): bool
    {
        return static::OPERATION_NAME === $operation;
    }
}

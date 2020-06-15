<?php

namespace App\Service\ChannelManager\SoapOta\Operation\V2007B;

use App\Exception\AccessDeniedException;
use App\Exception\DateFormatException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\Availability;
use App\Model\ProductAvailabilityCollection;
use App\Model\RestrictionStatus;
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
 * Class HotelAvailNotifOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class HotelAvailNotifOperation implements SoapOtaOperationInterface
{
    public const OPERATION_NAME = 'OTA_HotelAvailNotifRQ';

    /**
     *
     * @var BookingEngineInterface
     */
    private $bookingEngine;

    /**
     *
     * @var ProductLoader
     */
    private $productLoader;

    /**
     *
     * @var PartnerLoader
     */
    private $partnerLoader;

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
     * HotelAvailNotifOperation constructor.
     *
     * @param BookingEngineInterface        $bookingEngine
     * @param ProductLoader                 $productLoader
     * @param PartnerLoader                 $partnerLoader
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param CmhubLogger                   $logger
     */
    public function __construct(BookingEngineInterface $bookingEngine, ProductLoader $productLoader, PartnerLoader $partnerLoader, AuthorizationCheckerInterface $authorizationChecker, CmhubLogger $logger)
    {
        $this->bookingEngine = $bookingEngine;
        $this->productLoader = $productLoader;
        $this->partnerLoader = $partnerLoader;
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
     * @throws ValidationException
     */
    public function handle(\StdClass $request): array
    {
        $partner = $this->partnerLoader->find($hotelCode = $request->AvailStatusMessages->HotelCode);
        if (!$partner || !$this->authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)) {
            throw new AccessDeniedException(Response::HTTP_FORBIDDEN, $hotelCode);
        }

        $productAvailabilityCollection = new ProductAvailabilityCollection($partner);
        $inventories = Util::toArray($request->AvailStatusMessages->AvailStatusMessage);

        foreach ($inventories as $inventory) {
            if (!$product = $this->productLoader->find($partner, $inventory->StatusApplicationControl->InvTypeCode)) {
                $this->logger->addOperationException('', new ProductNotFoundException($partner, $inventory->StatusApplicationControl->InvTypeCode ?? ''), $this);
                continue;
            }

            $availability = new Availability($product);

            $startDate = \DateTime::createFromFormat('Y-m-d', $inventory->StatusApplicationControl->Start);
            $endDate = \DateTime::createFromFormat('Y-m-d', $inventory->StatusApplicationControl->End);
            if ($startDate > $endDate) {
                throw new ValidationException('Start date cannot be greater than end date');
            }

            if (!$startDate || !$endDate) {
                throw new DateFormatException('Y-m-d');
            }

            if (isset($inventory->BookingLimit) && ($inventory->BookingLimit < 0 || $inventory->BookingLimit > 9999)) {
                throw new ValidationException('The value of the stock cannot be less than 0 nor more than 9999');
            }

            $availability
                ->setStart($startDate)
                ->setEnd($endDate);

            if (isset($inventory->BookingLimit)) {
                $availability->setStock($inventory->BookingLimit);
            }

            if (isset($inventory->RestrictionStatus, $inventory->RestrictionStatus->Status) && RestrictionStatus::CLOSE === $inventory->RestrictionStatus->Status) {
                $availability->setStopSale(true);
            }

            if (isset($inventory->RestrictionStatus, $inventory->RestrictionStatus->Status) && RestrictionStatus::OPEN === $inventory->RestrictionStatus->Status) {
                $availability->setStopSale(false);
            }

//            When element is without stock and stop sale (only setting minimum stay) then skip:
//            <AvailStatusMessage>
//                <StatusApplicationControl Start="2020-11-09" End="2020-11-28" InvTypeCode="504963" RatePlanCode="SBX" />
//                <LengthsOfStay>
//                    <LengthOfStay MinMaxMessageType="SetMinLOS" Time="1" />
//                </LengthsOfStay>
//            </AvailStatusMessage>
            if (null === $availability->isStopSale() && null === $availability->getStock()) {
                continue;
            }

            $productAvailabilityCollection->addAvailability($availability);
        }

        $this->bookingEngine->updateAvailability($productAvailabilityCollection);

        $this->logger->addOperationInfo(LogAction::UPDATE_AVAILABILITY, $partner, $this);

        return [];
    }

    /**
     *
     * @param string $operation The operation
     *
     * @return boolean
     */
    public function supports(string $operation): bool
    {
        return static::OPERATION_NAME === $operation;
    }
}

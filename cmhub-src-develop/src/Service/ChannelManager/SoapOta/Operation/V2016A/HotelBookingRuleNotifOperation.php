<?php

namespace App\Service\ChannelManager\SoapOta\Operation\V2016A;

use App\Exception\AccessDeniedException;
use App\Exception\CmHubException;
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
use App\Model\RestrictionStatus;
use App\Model\RestrictionType;

/**
 * Class HotelBookingRuleNotifOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class HotelBookingRuleNotifOperation implements SoapOtaOperationInterface
{
    const OPERATION_NAME = 'OTA_HotelBookingRuleNotifRQ';

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
     * HotelBookingRuleNotifOperation constructor.
     *
     * @param BookingEngineInterface $bookingEngine
     * @param PartnerLoader $partnerLoader
     * @param ProductLoader $productLoader
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param CmhubLogger $logger
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
     * @throws CmHubException
     */
    public function handle(\StdClass $request): array
    {
        $partner = $this->partnerLoader->find($hotelCode = $request->RuleMessages->HotelCode);
        if (!$this->authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)) {
            throw new AccessDeniedException(Response::HTTP_FORBIDDEN, $hotelCode);
        }

        $ruleMessages = Util::toArray($request->RuleMessages->RuleMessage);
        $productAvailabilityCollection = new ProductAvailabilityCollection($partner);

        foreach ($ruleMessages as $ruleMessage) {
            if (!$product = $this->productLoader->find($partner, $ruleMessage->StatusApplicationControl->InvTypeCode)) {
                $this->logger->addOperationException($partner->getId(), new ProductNotFoundException($partner, $ruleMessage->StatusApplicationControl->InvTypeCode), $this);
                continue;
            }

            $startDate = \DateTime::createFromFormat('Y-m-d', $ruleMessage->StatusApplicationControl->Start);
            $endDate = \DateTime::createFromFormat('Y-m-d', $ruleMessage->StatusApplicationControl->End);
            if ($startDate > $endDate) {
                throw new ValidationException('Start date cannot be greater than end date');
            }
            if (!$startDate || !$endDate) {
                throw new DateFormatException('Y-m-d');
            }

            $productAvailabilityCollection->addEnabledWeekDays($ruleMessage->StatusApplicationControl);

            $availability = new Availability($product);
            $availability
                ->setStart($startDate)
                ->setEnd($endDate);

            $bookingRules = Util::toArray($ruleMessage->BookingRules->BookingRule);
            foreach ($bookingRules as $bookingRule) {
                $restrictionStatuses = Util::toArray($bookingRule->RestrictionStatus);
                foreach ($restrictionStatuses as $restrictionStatus) {
                    if (in_array($restrictionStatus->Restriction, RestrictionType::ALL) && RestrictionStatus::CLOSE === $restrictionStatus->Status) {
                        $availability->setStopSale(true);
                        break 2;
                    }

                    if (in_array($restrictionStatus->Restriction, RestrictionType::ALL) && RestrictionStatus::OPEN === $restrictionStatus->Status) {
                        $availability->setStopSale(false);
                        break 2;
                    }
                }
            }

            $productAvailabilityCollection->addAvailability($availability);
            $productAvailabilityCollection->setEnabledWeekDays([]);
        }

        $this->bookingEngine->updateAvailability($productAvailabilityCollection);

        $this->logger->addOperationInfo(LogAction::UPDATE_AVAILABILITY, $partner, $this);

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
        return static::OPERATION_NAME === $operation;
    }
}

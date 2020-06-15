<?php

namespace App\Service\ChannelManager\SoapOta\Operation\V2007B;

use App\Exception\AccessDeniedException;
use App\Exception\DateFormatException;
use App\Exception\NormalizerNotFoundException;
use App\Exception\ValidationException;
use App\Security\Voter\PartnerVoter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\SoapOta\Serializer\SoapSerializer;
use App\Service\ChannelManager\SoapOta\SoapOtaOperationInterface;
use App\Service\Loader\PartnerLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class HotelRatePlanOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class HotelRatePlanOperation implements SoapOtaOperationInterface
{
    public const OPERATION_NAME = 'OTA_HotelRatePlanRQ';

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
     * @var SoapSerializer
     */
    private $soapSerializer;

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
     * HotelRatePlanOperation constructor.
     *
     * @param BookingEngineInterface        $bookingEngine
     * @param PartnerLoader                 $partnerLoader
     * @param SoapSerializer                $soapSerializer
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param CmhubLogger                   $logger
     */
    public function __construct(BookingEngineInterface $bookingEngine, PartnerLoader $partnerLoader, SoapSerializer $soapSerializer, AuthorizationCheckerInterface $authorizationChecker, CmhubLogger $logger)
    {
        $this->bookingEngine = $bookingEngine;
        $this->partnerLoader = $partnerLoader;
        $this->soapSerializer = $soapSerializer;
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
     * @throws NormalizerNotFoundException
     */
    public function handle(\StdClass $request): array
    {
        $partner = $this->partnerLoader->find($hotelCode = $request->RatePlans->RatePlan->HotelRef->HotelCode);
        if (!$partner || !$this->authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)) {
            throw new AccessDeniedException(Response::HTTP_FORBIDDEN, $hotelCode);
        }

        $startDate = $endDate = new \DateTime();
        if (isset($request->RatePlans->RatePlan->DateRange->Start, $request->RatePlans->RatePlan->DateRange->End)) {
            $startDate = \DateTime::createFromFormat('Y-m-d', $request->RatePlans->RatePlan->DateRange->Start);
            $endDate = \DateTime::createFromFormat('Y-m-d', $request->RatePlans->RatePlan->DateRange->End);
        }

        if ($startDate > $endDate) {
            throw new ValidationException('Start date cannot be greater than end date');
        }

        if (!$startDate || !$endDate) {
            throw new DateFormatException('Y-m-d');
        }

        $rates = $this->bookingEngine->getRates($partner, $startDate, $endDate);

        $this->logger->addOperationInfo(LogAction::GET_RATES, $partner, $this);

        return $this->soapSerializer->normalize($rates);
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

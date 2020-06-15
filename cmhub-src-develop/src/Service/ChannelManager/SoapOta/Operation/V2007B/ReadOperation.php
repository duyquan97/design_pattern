<?php

namespace App\Service\ChannelManager\SoapOta\Operation\V2007B;

use App\Exception\AccessDeniedException;
use App\Exception\DateFormatException;
use App\Exception\NormalizerNotFoundException;
use App\Exception\PartnerNotFoundException;
use App\Exception\ValidationException;
use App\Model\OTADateType;
use App\Security\Voter\PartnerVoter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\SoapOta\Serializer\SoapSerializer;
use App\Service\ChannelManager\SoapOta\SoapOtaOperationInterface;
use App\Service\Loader\PartnerLoader;
use App\Utils\EasyDateTime;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class ReadOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ReadOperation implements SoapOtaOperationInterface
{
    public const OPERATION_NAME = 'OTA_ReadRQ';

    /**
     *
     * @var BookingEngineInterface
     */
    private $bookingEngine;

    /**
     *
     * @var SoapSerializer
     */
    private $soapSerializer;

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
     * ReadOperation constructor.
     *
     * @param BookingEngineInterface        $bookingEngine
     * @param SoapSerializer                $soapSerializer
     * @param PartnerLoader                 $partnerLoader
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param CmhubLogger                   $logger
     */
    public function __construct(BookingEngineInterface $bookingEngine, SoapSerializer $soapSerializer, PartnerLoader $partnerLoader, AuthorizationCheckerInterface $authorizationChecker, CmhubLogger $logger)
    {
        $this->bookingEngine = $bookingEngine;
        $this->soapSerializer = $soapSerializer;
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
     * @throws PartnerNotFoundException
     * @throws AccessDeniedException
     * @throws DateFormatException
     * @throws ValidationException
     * @throws NormalizerNotFoundException
     */
    public function handle(\StdClass $request): array
    {
        $partner = $this->partnerLoader->find($hotelCode = $request->ReadRequests->HotelReadRequest->HotelCode);
        if (!$partner || !$this->authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)) {
            throw new AccessDeniedException(Response::HTTP_FORBIDDEN, $hotelCode);
        }

        $startDate = EasyDateTime::createFromFormats(
            $request->ReadRequests->HotelReadRequest->SelectionCriteria->Start,
            [
                'Y-m-d',
                \DateTime::ATOM,
            ]
        );
        $endDate = EasyDateTime::createFromFormats(
            $request->ReadRequests->HotelReadRequest->SelectionCriteria->End,
            [
                'Y-m-d',
                \DateTime::ATOM,
            ]
        );

        $dateType = $request->ReadRequests->HotelReadRequest->SelectionCriteria->DateType;
        if (empty($dateType)) {
            $dateType = OTADateType::LAST_UPDATE_DATE;
        }

        if (false === in_array($dateType, OTADateType::DATE_TYPE)) {
            throw new ValidationException('DateType invalid');
        }

        if ($startDate > $endDate) {
            throw new ValidationException('Start date cannot be greater than end date');
        }

        if (!$startDate || !$endDate) {
            throw new DateFormatException(sprintf('%s | %s', 'Y-m-d', \DateTime::ISO8601));
        }

        $bookings = $this->bookingEngine->getBookings($startDate, $endDate, null, [$partner], $dateType);

        $this->logger->addOperationInfo(LogAction::GET_BOOKINGS, $partner, $this);

        return $this->soapSerializer->normalize($bookings);
    }

    /**
     *
     * @param  string $operation The operation
     *
     * @return boolean
     */
    public function supports(string $operation): bool
    {
        return static::OPERATION_NAME === $operation;
    }
}

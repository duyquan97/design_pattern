<?php

namespace App\Service\ChannelManager\SoapOta\Operation\V2007B;

use App\Exception\AccessDeniedException;
use App\Exception\DateFormatException;
use App\Exception\NormalizerNotFoundException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\ProductInterface;
use App\Security\Voter\PartnerVoter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\SoapOta\Serializer\SoapSerializer;
use App\Service\ChannelManager\SoapOta\SoapOtaOperationInterface;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use App\Utils\Util;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class HotelInvCountOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class HotelInvCountOperation implements SoapOtaOperationInterface
{
    public const OPERATION_NAME = 'OTA_HotelInvCountRQ';

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
     * @var SoapSerializer
     */
    private $soapSerializer;

    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     * HotelInvCountOperation constructor.
     *
     * @param BookingEngineInterface        $bookingEngine
     * @param ProductLoader                 $productLoader
     * @param PartnerLoader                 $partnerLoader
     * @param SoapSerializer                $soapSerializer
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param CmhubLogger                   $logger
     */
    public function __construct(BookingEngineInterface $bookingEngine, ProductLoader $productLoader, PartnerLoader $partnerLoader, SoapSerializer $soapSerializer, AuthorizationCheckerInterface $authorizationChecker, CmhubLogger $logger)
    {
        $this->bookingEngine = $bookingEngine;
        $this->productLoader = $productLoader;
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
     * @throws ProductNotFoundException
     * @throws ValidationException
     * @throws NormalizerNotFoundException
     */
    public function handle(\StdClass $request): array
    {
        $partner = $this->partnerLoader->find($hotelCode = $request->HotelInvCountRequests->HotelInvCountRequest->HotelRef->HotelCode);
        if (!$partner || !$this->authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)) {
            throw new AccessDeniedException(Response::HTTP_FORBIDDEN, $hotelCode);
        }

        $startDate = \DateTime::createFromFormat('Y-m-d', $request->HotelInvCountRequests->HotelInvCountRequest->DateRange->Start);
        $endDate = \DateTime::createFromFormat('Y-m-d', $request->HotelInvCountRequests->HotelInvCountRequest->DateRange->End);
        if ($startDate > $endDate) {
            throw new ValidationException('Start date cannot be greater than end date');
        }
        if (!$startDate || !$endDate) {
            throw new DateFormatException('Y-m-d');
        }

        $roomTypes = $request->HotelInvCountRequests->HotelInvCountRequest->RoomTypeCandidates->RoomTypeCandidate;
        $roomTypesArray = array_column(
            Util::toArray($roomTypes),
            'RoomTypeCode'
        );

        $products = $this
            ->productLoader
            ->getProductsByRoomCode(
                $partner,
                $roomTypesArray
            );

        $productsArray = array_map(
            function (ProductInterface $product) {
                return $product->getIdentifier();
            },
            $products
        );

        $roomsNotFoundArray = array_diff($roomTypesArray, $productsArray);

        if ([] !== $roomsNotFoundArray) {
            throw new ProductNotFoundException($partner, $roomsNotFoundArray[key($roomsNotFoundArray)]);
        }

        $availabilities = $this->bookingEngine->getAvailabilities($partner, $startDate, $endDate, $products);

        $this->logger->addOperationInfo(LogAction::GET_AVAILABILITY, $partner, $this);

        return $this->soapSerializer->normalize($availabilities);
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

<?php

namespace App\Service\ChannelManager\SoapOta\Operation\V2015A;

use App\Exception\AccessDeniedException;
use App\Exception\DateFormatException;
use App\Exception\NormalizerNotFoundException;
use App\Exception\ValidationException;
use App\Model\Factory\ProductCollectionFactory;
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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class HotelAvailGetOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class HotelAvailGetOperation implements SoapOtaOperationInterface
{
    public const OPERATION_NAME = 'OTA_HotelAvailGetRQ';

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
     * @var SoapSerializer
     */
    private $soapSerializer;

    /**
     *
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     * HotelAvailGetOperation constructor.
     *
     * @param BookingEngineInterface        $bookingEngine
     * @param PartnerLoader                 $partnerLoader
     * @param ProductLoader                 $productLoader
     * @param SoapSerializer                $soapSerializer
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param ProductCollectionFactory      $productCollectionFactory
     * @param CmhubLogger                   $logger
     */
    public function __construct(BookingEngineInterface $bookingEngine, PartnerLoader $partnerLoader, ProductLoader $productLoader, SoapSerializer $soapSerializer, AuthorizationCheckerInterface $authorizationChecker, ProductCollectionFactory $productCollectionFactory, CmhubLogger $logger)
    {
        $this->bookingEngine = $bookingEngine;
        $this->partnerLoader = $partnerLoader;
        $this->productLoader = $productLoader;
        $this->soapSerializer = $soapSerializer;
        $this->authorizationChecker = $authorizationChecker;
        $this->productCollectionFactory = $productCollectionFactory;
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
        $partner = $this->partnerLoader->find($hotelCode = $request->HotelAvailRequests->HotelAvailRequest->HotelRef->HotelCode);
        if (!$partner || !$this->authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)) {
            throw new AccessDeniedException(Response::HTTP_FORBIDDEN, $hotelCode);
        }

        $products = $this->productCollectionFactory->create($partner);
        if (isset($request->HotelAvailRequests->HotelAvailRequest->RoomTypeCandidates)) {
            $roomCandidates = Util::toArray($request->HotelAvailRequests->HotelAvailRequest->RoomTypeCandidates);
            foreach ($roomCandidates as $roomCandidate) {
                if (!isset($roomCandidate->RoomTypeCandidate->RoomTypeCode)) {
                    continue;
                }

                if ($product = $this->productLoader->find($partner, $roomCandidate->RoomTypeCandidate->RoomTypeCode)) {
                    $products->addProduct($product);
                }
            }
        }

        if ($products->isEmpty()) {
            $products = $this->productLoader->getByPartner($partner);
        }

        $startDate = \DateTime::createFromFormat('Y-m-d', $request->HotelAvailRequests->HotelAvailRequest->DateRange->Start);
        $endDate = \DateTime::createFromFormat('Y-m-d', $request->HotelAvailRequests->HotelAvailRequest->DateRange->End);
        if ($startDate > $endDate) {
            throw new ValidationException('Start date cannot be greater than end date');
        }

        if (!$startDate || !$endDate) {
            throw new DateFormatException('Y-m-d');
        }

        $this->logger->addOperationInfo(LogAction::GET_PRODUCTS, $partner, $this);

        $availabilities = $this->bookingEngine->getAvailabilities($partner, $startDate, $endDate, $products->toArray());

        return $this
            ->soapSerializer
            ->normalize(
                $products,
                [
                    'start'           => $startDate,
                    'end'             => $endDate,
                    'targetOperation' => self::OPERATION_NAME,
                    'availabilities'  => $availabilities,
                ]
            );
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

<?php

namespace App\Service\ChannelManager\SoapOta\Operation\V2010A;

use App\Exception\AccessDeniedException;
use App\Exception\DateFormatException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Security\Voter\PartnerVoter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\SoapOta\Serializer\V2010A\ProductRateCollectionNormalizer;
use App\Service\ChannelManager\SoapOta\SoapOtaOperationInterface;
use App\Service\Loader\PartnerLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use App\Utils\Util;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class HotelRateAmountNotifOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class HotelRateAmountNotifOperation implements SoapOtaOperationInterface
{
    public const OPERATION_NAME = 'OTA_HotelRateAmountNotifRQ';

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
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var CmhubLogger
     */
    private $logger;

    /**
     * @var ProductRateCollectionNormalizer
     */
    private $productRateCollectionNormalizer;

    /**
     * HotelRateAmountNotifOperation constructor.
     *
     * @param BookingEngineInterface          $bookingEngine
     * @param PartnerLoader                   $partnerLoader
     * @param AuthorizationCheckerInterface   $authorizationChecker
     * @param CmhubLogger                     $logger
     * @param ProductRateCollectionNormalizer $productRateCollectionNormalizer
     */
    public function __construct(BookingEngineInterface $bookingEngine, PartnerLoader $partnerLoader, AuthorizationCheckerInterface $authorizationChecker, CmhubLogger $logger, ProductRateCollectionNormalizer $productRateCollectionNormalizer)
    {
        $this->bookingEngine = $bookingEngine;
        $this->partnerLoader = $partnerLoader;
        $this->authorizationChecker = $authorizationChecker;
        $this->logger = $logger;
        $this->productRateCollectionNormalizer = $productRateCollectionNormalizer;
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
        $partner = $this->partnerLoader->find($hotelCode = $request->RateAmountMessages->HotelCode);
        if (!$partner || !$this->authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)) {
            throw new AccessDeniedException(Response::HTTP_FORBIDDEN, $hotelCode);
        }

        $rateAmountMessages = Util::toArray($request->RateAmountMessages->RateAmountMessage);

        $this->bookingEngine->updateRates($this->productRateCollectionNormalizer->denormalize($rateAmountMessages, ['partner' => $partner]));

        $this->logger->addOperationInfo(LogAction::UPDATE_RATES, $partner, $this);

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

<?php

namespace App\Service\ChannelManager\SoapOta\Operation\V2016A;

use App\Exception\AccessDeniedException;
use App\Exception\NormalizerNotFoundException;
use App\Model\ProductRateCollection;
use App\Security\Voter\PartnerVoter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\SoapOta\Serializer\SoapSerializer;
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
    const OPERATION_NAME = 'OTA_HotelRateAmountNotifRQ';

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
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     * @var SoapSerializer
     */
    private $soapSerializer;

    /**
     * HotelRateAmountNotifOperation constructor.
     *
     * @param BookingEngineInterface        $bookingEngine
     * @param PartnerLoader                 $partnerLoader
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param CmhubLogger                   $logger
     * @param SoapSerializer                $soapSerializer
     */
    public function __construct(BookingEngineInterface $bookingEngine, PartnerLoader $partnerLoader, AuthorizationCheckerInterface $authorizationChecker, CmhubLogger $logger, SoapSerializer $soapSerializer)
    {
        $this->bookingEngine = $bookingEngine;
        $this->partnerLoader = $partnerLoader;
        $this->authorizationChecker = $authorizationChecker;
        $this->logger = $logger;
        $this->soapSerializer = $soapSerializer;
    }

    /**
     *
     * @param \StdClass $request
     *
     * @return array
     *
     * @throws AccessDeniedException
     * @throws NormalizerNotFoundException
     */
    public function handle(\StdClass $request): array
    {
        $partner = $this->partnerLoader->find($hotelCode = $request->RateAmountMessages->HotelCode);
        if (!$this->authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)) {
            throw new AccessDeniedException(Response::HTTP_FORBIDDEN, $hotelCode);
        }

        $rateAmountMessages = Util::toArray($request->RateAmountMessages->RateAmountMessage);

        /** @var ProductRateCollection $productRateCollection */
        $productRateCollection = $this->soapSerializer->denormalize($rateAmountMessages, ProductRateCollection::class, ['partner' => $partner]);

        $this->bookingEngine->updateRates($productRateCollection);
        $this->logger->addOperationInfo(LogAction::UPDATE_RATES, $partner, $this);

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

<?php

namespace App\Service\ChannelManager\SoapOta\Operation\V2007B;

use App\Exception\AccessDeniedException;
use App\Exception\DateFormatException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\ProductRate;
use App\Model\ProductRateCollection;
use App\Model\Rate;
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
     * HotelRateAmountNotifOperation constructor.
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
        $partner = $this->partnerLoader->find($hotelCode = $request->RateAmountMessages->HotelCode);
        if (!$partner || !$this->authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)) {
            throw new AccessDeniedException(Response::HTTP_FORBIDDEN, $hotelCode);
        }

        $productRateCollection = new ProductRateCollection($partner);
        $rateAmountMessages = Util::toArray($request->RateAmountMessages->RateAmountMessage);

        foreach ($rateAmountMessages as $rateAmountMessage) {
            if (!isset($rateAmountMessage->StatusApplicationControl->InvTypeCode)) {
                throw new ValidationException('"InvTypeCode" is mandatory');
            }

            $product = $this->productLoader->find($partner, $productCode = $rateAmountMessage->StatusApplicationControl->InvTypeCode);
            if (!$product) {
                throw new ProductNotFoundException($partner, $productCode);
            }

            $productRate = new ProductRate($product);
            $rates = Util::toArray($rateAmountMessage->Rates->Rate);
            foreach ($rates as $rate) {
                if (!isset($rate->Start) || !isset($rate->End)) {
                    throw new ValidationException('"Start" and "End" are mandatory');
                }

                $startDate = \DateTime::createFromFormat('Y-m-d', $rate->Start);
                $endDate = \DateTime::createFromFormat('Y-m-d', $rate->End);
                if ($startDate > $endDate) {
                    throw new ValidationException('Start date cannot be greater than end date');
                }
                if (!$startDate || !$endDate) {
                    throw new DateFormatException('Y-m-d');
                }

                $amount = floatval($rate->BaseByGuestAmts->BaseByGuestAmt->AmountAfterTax);
                if ($amount < 0) {
                    throw new ValidationException('The amount cannot be negative');
                }

                $rateModel = (new Rate())
                    ->setStart($startDate)
                    ->setEnd($endDate)
                    ->setAmount($amount);

                $productRate->addRate($rateModel);
            }

            $productRateCollection->addProductRate($productRate);
        }

        $this->bookingEngine->updateRates($productRateCollection);

        $this->logger->addOperationInfo(LogAction::UPDATE_RATES, $partner, $this);

        return [];
    }

    /**
     * @param  string $operation The operation
     *
     * @return boolean
     */
    public function supports(string $operation): bool
    {
        return static::OPERATION_NAME === $operation;
    }
}

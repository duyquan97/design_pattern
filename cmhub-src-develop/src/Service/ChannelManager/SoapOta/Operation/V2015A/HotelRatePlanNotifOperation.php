<?php

namespace App\Service\ChannelManager\SoapOta\Operation\V2015A;

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
 * Class HotelRatePlanNotifOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class HotelRatePlanNotifOperation implements SoapOtaOperationInterface
{
    public const OPERATION_NAME = 'OTA_HotelRatePlanNotifRQ';

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
     * HotelRatePlanNotifOperation constructor.
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
        $partner = $this->partnerLoader->find($hotelCode = $request->RatePlans->HotelCode);
        if (!$partner || !$this->authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)) {
            throw new AccessDeniedException(Response::HTTP_FORBIDDEN, $hotelCode);
        }

        $productRateCollection = new ProductRateCollection($partner);
        $ratePlans = Util::toArray($request->RatePlans->RatePlan);

        foreach ($ratePlans as $ratePlan) {
            $startDate = \DateTime::createFromFormat('Y-m-d', $ratePlan->Start);
            $endDate = \DateTime::createFromFormat('Y-m-d', $ratePlan->End);
            if ($startDate > $endDate) {
                throw new ValidationException('Start date cannot be greater than end date');
            }

            if (!$startDate || !$endDate) {
                throw new DateFormatException('Y-m-d');
            }

            $rates = Util::toArray($ratePlan->Rates->Rate);

            foreach ($rates as $rate) {
                $product = $this->productLoader->find($partner, $productCode = $rate->InvTypeCode);
                if (!$product) {
                    throw new ProductNotFoundException($partner, $productCode);
                }

                $productRate = new ProductRate($product);

                $amount = $this->getRateAmount($rate);

                if (null === $amount) {
                    throw new ValidationException('Can not consume price');
                }

                if ($amount < 0) {
                    throw new ValidationException('The amount cannot be negative');
                }

                $rateModel = (new Rate())
                    ->setStart($startDate)
                    ->setEnd($endDate)
                    ->setAmount($amount);

                $productRate->addRate($rateModel);

                $productRateCollection->addProductRate($productRate);
            }
        }

        $this->bookingEngine->updateRates($productRateCollection);

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

    /**
     * @param mixed $rate
     *
     * @return float
     */
    private function getRateAmount($rate): ?float
    {
        if (is_array($rate->BaseByGuestAmts->BaseByGuestAmt)) {
            foreach ($rate->BaseByGuestAmts->BaseByGuestAmt as $amount) {
                if (2 === $amount->NumberOfGuests) {
                    return floatval($amount->AmountAfterTax);
                }
            }

            return null;
        }

        return floatval($rate->BaseByGuestAmts->BaseByGuestAmt->AmountAfterTax);
    }
}

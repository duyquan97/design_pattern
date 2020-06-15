<?php

namespace App\Service\ChannelManager\AvailPro;

use App\Entity\Booking;
use App\Entity\ChannelManager;
use App\Exception\PartnerNotFoundException;
use App\Exception\ValidationException;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Model\ProductRateCollection;
use App\Model\Rate;
use App\Model\RatePlanCode;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\AvailPro\Serializer\AvailProSerializer;
use App\Service\ChannelManager\ChannelManagerList;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class AvailProIntegration
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailProIntegration
{
    const GET_HOTEL_TEMPLATE = 'Api/Ext/Xml/AvailPro/V1/GetHotel.xml.twig';
    const GET_BOOKINGS_TEMPLATE = 'Api/Ext/Xml/AvailPro/V1/GetBookings.xml.twig';
    const GET_BOOKINGS_ALL_PARTNERS_TEMPLATE = 'Api/Ext/Xml/AvailPro/V1/GetBookingsAllPartner.xml.twig';
    const FAILURE_TEMPLATE = 'Api/Ext/Xml/AvailPro/V1/Failure.xml.twig';
    const SUCCESS_TEMPLATE = 'Api/Ext/Xml/AvailPro/V1/Success.xml.twig';

    /**
     *
     * @var Environment
     */
    private $templating;

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
     * @var AvailProSerializer
     */
    private $availProSerializer;

    /**
     *
     * @var BookingEngineInterface
     */
    private $bookingEngine;

    /**
     * @var CmhubLogger
     */
    private $logger;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;


    /**
     * AvailProIntegration constructor.
     *
     * @param Environment            $templating
     * @param ProductLoader          $productLoader
     * @param PartnerLoader          $partnerLoader
     * @param AvailProSerializer     $availProSerializer
     * @param BookingEngineInterface $bookingEngine
     * @param CmhubLogger            $logger
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(Environment $templating, ProductLoader $productLoader, PartnerLoader $partnerLoader, AvailProSerializer $availProSerializer, BookingEngineInterface $bookingEngine, CmhubLogger $logger, EntityManagerInterface $entityManager)
    {
        $this->templating = $templating;
        $this->productLoader = $productLoader;
        $this->partnerLoader = $partnerLoader;
        $this->availProSerializer = $availProSerializer;
        $this->bookingEngine = $bookingEngine;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    /**
     *
     * @param string $identifier
     *
     * @return string
     */
    public function getHotel(?string $identifier): string
    {
        try {
            if (null === $identifier) {
                throw new PartnerNotFoundException($identifier);
            }

            $partner = $this->partnerLoader->find($identifier);
            if (!$partner) {
                throw new PartnerNotFoundException($identifier);
            }

            $products = $this->productLoader->getByPartner($partner);

            $this
                ->logger
                ->addOperationInfo(
                    LogAction::GET_PRODUCTS,
                    $partner,
                    $this
                );

            return $this->templating->render(
                static::GET_HOTEL_TEMPLATE,
                [
                    'ratePlan'       => RatePlanCode::SBX,
                    'ratePlanName'   => Rate::SBX_RATE_PLAN_NAME,
                    'ratePlanRegime' => Rate::SBC_PLAN_REGIME,
                    'partner'        => $partner,
                    'products'       => $products,
                ]
            );
        } catch (\Exception $exception) {
            $this->logger->addOperationException(
                LogAction::GET_PRODUCTS,
                $exception,
                $this
            );

            return $this->renderFailureTemplate($exception);
        }
    }

    /**
     *
     * @param \stdClass $request
     *
     * @return string
     */
    public function updateAvailabilitiesAndRates(\stdClass $request): string
    {
        try {
            if (!is_array($request->inventoryUpdate->room)) {
                $request->inventoryUpdate->room = [$request->inventoryUpdate->room];
            }

            /* @var ProductAvailabilityCollectionInterface $availabilityCollection */
            $availabilityCollection = $this->availProSerializer->denormalize($request, ProductAvailabilityCollection::class);
            /* @var ProductRateCollection $rateCollection */
            $rateCollection = $this->availProSerializer->denormalize($request, ProductRateCollection::class);

            if (!$availabilityCollection->isEmpty()) {
                $this->bookingEngine->updateAvailability($availabilityCollection);
            }

            if (!$rateCollection->isEmpty()) {
                $this->bookingEngine->updateRates($rateCollection);
            }

            $this
                ->logger
                ->addOperationInfo(
                    LogAction::UPDATE_DATA,
                    $availabilityCollection->getPartner(),
                    $this
                );

            return $this->renderSuccessTemplate();
        } catch (\Exception $exception) {
            $this->logger->addOperationException(
                LogAction::UPDATE_DATA,
                $exception,
                $this
            );

            return $this->renderFailureTemplate($exception);
        }
    }

    /**
     *
     * @param DateTime $start
     * @param DateTime $end
     * @param string   $identifier
     *
     * @return string
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getBookings(DateTime $start, DateTime $end, string $identifier): string
    {
        try {
            if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2]\d|3[0-1])$/', $start->format('Y-m-d'))) {
                throw new ValidationException('Date format has to be Y-m-d');
            }

            if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2]\d|3[0-1])$/', $end->format('Y-m-d'))) {
                throw new ValidationException('Date format has to be Y-m-d');
            }

            if ($start > $end) {
                throw new ValidationException('End Date must be greater than start date');
            }

            $partner = $this->partnerLoader->find($identifier);
            /** @var ChannelManager $channelManager */
            $channelManager = $this->entityManager
                ->getRepository(ChannelManager::class)
                ->findOneBy([
                    'identifier' => ChannelManagerList::AVAILPRO,
                ]);

            if ($partner) {
                $bookings = $this->bookingEngine->getBookings($start, $end, $channelManager, [$partner]);

                $this->logger->addOperationInfo(
                    LogAction::GET_BOOKINGS,
                    $partner,
                    $this
                );

                return $this->templating->render(
                    static::GET_BOOKINGS_TEMPLATE,
                    [
                        'partner'  => $partner,
                        'bookings' => $bookings,
                        'ratePlan' => RatePlanCode::SBX,
                    ]
                );
            }

            if (!$partner) {
                $bookings = $this->bookingEngine->getBookings($start, $end, $channelManager)->getBookings();
                $bookingsAllPartner = [];
                /** @var Booking $booking */
                foreach ($bookings as $booking) {
                    $bookingsAllPartner[$booking->getPartner()->getIdentifier()][] = $booking;
                }

                $this->logger->addOperationInfo(
                    LogAction::GET_BOOKINGS,
                    $partner,
                    $this
                );

                return $this->templating->render(
                    static::GET_BOOKINGS_ALL_PARTNERS_TEMPLATE,
                    [
                        'bookingsAllPartner' => $bookingsAllPartner,
                        'ratePlan'           => RatePlanCode::SBX,
                    ]
                );
            }
        } catch (BadRequestHttpException $exception) {
            $this->logger->addOperationException(
                LogAction::GET_BOOKINGS,
                $exception,
                $this
            );

            return $this->templating->render(
                static::FAILURE_TEMPLATE,
                [
                    'code'    => $exception->getStatusCode(),
                    'message' => $exception->getMessage(),
                ]
            );
        } catch (\Exception $exception) {
            $this->logger->addOperationException(
                LogAction::GET_BOOKINGS,
                $exception,
                $this
            );

            return $this->renderFailureTemplate($exception);
        }
    }

    /**
     *
     * @return string
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function renderSuccessTemplate(): string
    {
        return $this->templating->render(static::SUCCESS_TEMPLATE);
    }

    /**
     *
     * @param \Exception $exception
     *
     * @return string
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function renderFailureTemplate(\Exception $exception): string
    {
        return $this->templating->render(
            static::FAILURE_TEMPLATE,
            [
                'code'    => $exception->getCode(),
                'message' => $exception->getMessage(),
            ]
        );
    }
}

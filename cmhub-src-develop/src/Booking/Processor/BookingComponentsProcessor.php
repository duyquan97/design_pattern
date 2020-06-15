<?php

namespace App\Booking\Processor;

use App\Booking\BookingProcessorInterface;
use App\Model\BookingInterface;
use App\Service\Booking\JarvisClient;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogType;

/**
 * Class BookingComponentsProcessor
 *
 * This processor sets the booking details  picket from Jarvis service.
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingComponentsProcessor implements BookingProcessorInterface
{

    /**
     * @var JarvisClient $httpClient
     */
    private $httpClient;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     * BookingComponentsProcessor constructor.
     *
     * @param JarvisClient $client
     * @param CmhubLogger $logger
     *
     */
    public function __construct(JarvisClient $client, CmhubLogger $logger)
    {
        $this->httpClient = $client;
        $this->logger = $logger;
    }

    /**
     *
     * @param BookingInterface $booking
     *
     * @return BookingInterface
     */
    public function process(BookingInterface $booking): BookingInterface
    {
        if (!empty($booking->getComponents())) {
            return $booking->setComments(sprintf('%s. %s', $booking->getComments(), implode(' | ', $booking->getComponents())));
        }

        if (!$booking->isConfirmed()) {
            return $booking;
        }

        try {
            $response = $this->httpClient->getBookingDetails($booking->getReservationId());
        } catch (\Exception|\Throwable $e) {
            $this->logger->addRecord(
                \Monolog\Logger::CRITICAL,
                $e->getMessage(),
                [
                    LogKey::TYPE_KEY    => LogType::EXCEPTION_TYPE,
                    LogKey::EX_TYPE_KEY => 'unknown',
                    LogKey::MESSAGE_KEY => $e->getMessage(),
                ],
                $this
            );

            return $booking;
        }

        if (!isset($response->experience) || !isset($response->experience->components)) {
            return $booking;
        }

        $experienceComponent = [];
        foreach ($response->experience->components as $component) {
            $experienceComponent[] = $component->name;
        }

        $booking
            ->setComponents($experienceComponent)
            ->setComments($booking->getComments() . ' | ' . implode(' | ', $experienceComponent));

        return $booking;
    }
}

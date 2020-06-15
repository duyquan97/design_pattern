<?php

namespace App\Service\ChannelManager\Wubook;

use App\Entity\Booking;
use App\Exception\ChannelManagerClientException;
use App\Model\PushBooking;
use App\Service\ChannelManager\ChannelManagerInterface;
use App\Service\ChannelManager\Wubook\Serializer\BookingNormalizer;
use App\Utils\Monolog\CmhubLogger;
use GuzzleHttp\Client;
use GuzzleHttp;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class WubookChannelManager
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class WubookChannelManager implements ChannelManagerInterface
{
    public const NAME = 'wubook';

    /**
     * @var BookingNormalizer
     */
    private $bookingNormalizer;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var CmhubLogger
     */
    private $logger;

    /**
     * WubookChannelManager constructor.
     *
     * @param BookingNormalizer $bookingNormalizer
     * @param CmhubLogger $logger
     * @param Client $wubookHttpclient
     * @param string $wubookEndpoint
     */
    public function __construct(BookingNormalizer $bookingNormalizer, CmhubLogger $logger, Client $wubookHttpclient, string $wubookEndpoint)
    {
        $this->bookingNormalizer = $bookingNormalizer;
        $this->client = $wubookHttpclient;
        $this->endpoint = $wubookEndpoint;
        $this->logger = $logger;
    }

    /**
     * @param PushBooking $pushBooking
     *
     * @return void
     *
     * @throws ChannelManagerClientException
     */
    public function pushBookingRequest(PushBooking $pushBooking)
    {
        $booking = $pushBooking->getBooking();
        $transaction = $booking->getTransaction();
        $requestString = $this->getRequestBody($booking);

        try {
            if ($transaction) {
                $transaction->setRequest($requestString);
            }

            $response = $this->client->post(
                sprintf('%s%s', $this->endpoint, $booking->getPartner()->getIdentifier()),
                [
                    GuzzleHttp\RequestOptions::JSON => json_decode($requestString, true),
                ]
            );
            $responseContent = $response->getBody()->getContents();
            if ($transaction) {
                $transaction->setResponse($responseContent);
            }

            $this
                ->logger
                ->addPushBookingSent(
                    $pushBooking,
                    $requestString,
                    $responseContent
                );
        } catch (GuzzleException|\Exception $e) {
            $this->logger->addPushBookingException($e->getMessage(), $pushBooking, $this, $requestString);
            $booking->getTransaction()->setResponse($e->getMessage());

            throw new ChannelManagerClientException($e->getMessage(), (int) $e->getCode());
        }
    }

    /**
     * @param string $channelManagerCode
     *
     * @return bool
     */
    public function supports(string $channelManagerCode): bool
    {
        return static::NAME === $channelManagerCode;
    }

    /**
     * @param Booking $booking
     *
     * @return string
     */
    public function getRequestBody(Booking $booking): string
    {
        return json_encode($this->bookingNormalizer->normalize($booking));
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return 'application/json';
    }
}

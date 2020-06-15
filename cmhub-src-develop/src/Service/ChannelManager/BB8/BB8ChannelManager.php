<?php

namespace App\Service\ChannelManager\BB8;

use App\Entity\Booking;
use App\Exception\ChannelManagerClientException;
use App\Exception\CmHubException;
use App\Booking\Model\BookingStatus;
use App\Model\Factory\BookingCollectionFactory;
use App\Model\PushBooking;
use App\Service\ChannelManager\BB8\Serializer\BookingCollectionNormalizer;
use App\Service\ChannelManager\ChannelManagerInterface;
use App\Service\ChannelManager\ChannelManagerList;
use App\Utils\HttpClient;
use App\Utils\Monolog\CmhubLogger;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BB8ChannelManager
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BB8ChannelManager implements ChannelManagerInterface
{
    public const NAME = ChannelManagerList::BB8;

    /**
     *
     * @var HttpClient
     */
    private $bbEHttpClient;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     *
     * @var BookingCollectionNormalizer
     */
    private $bookingCollectionNormalizer;

    /**
     * @var BookingCollectionFactory
     */
    private $bookingCollectionFactory;

    /**
     * BB8ChannelManager constructor.
     *
     * @param HttpClient                  $bbEHttpClient
     * @param CmhubLogger                 $logger
     * @param BookingCollectionNormalizer $bookingCollectionNormalizer
     * @param BookingCollectionFactory    $bookingCollectionFactory
     */
    public function __construct(HttpClient $bbEHttpClient, CmhubLogger $logger, BookingCollectionNormalizer $bookingCollectionNormalizer, BookingCollectionFactory $bookingCollectionFactory)
    {
        $this->bbEHttpClient = $bbEHttpClient;
        $this->logger = $logger;
        $this->bookingCollectionNormalizer = $bookingCollectionNormalizer;
        $this->bookingCollectionFactory = $bookingCollectionFactory;
    }

    /**
     *
     * @param PushBooking $pushBooking
     *
     * @return void
     *
     * @throws CmHubException
     */
    public function pushBookingRequest(PushBooking $pushBooking)
    {
        $booking = $pushBooking->getBooking();

        try {
            $booking
                ->getTransaction()
                ->setRequest($body = $this->getRequestBody($booking));

            $method = BookingStatus::CANCELLED === $booking->getStatus() ? 'PUT' : 'POST';
            /** @var ResponseInterface $response */
            $response = $this->bbEHttpClient->call($method, $body, ['Content-Type' => 'application/json']);
            $responseContent = $response->getBody()->getContents();

            $booking
                ->getTransaction()
                ->setResponse($responseContent)
                ->setStatusCode((string) $response->getStatusCode());

            if ($responseContent && array_key_exists('error', json_decode($responseContent))) {
                $this->logger->addPushBookingException($responseContent, $pushBooking, $this, $body);
                throw new ChannelManagerClientException($responseContent, 400);
            }

            $this
                ->logger
                ->addPushBookingSent(
                    $pushBooking,
                    $body,
                    $responseContent
                );
        } catch (\Exception|GuzzleException $e) {
            $booking
                ->getTransaction()
                ->setResponse($e->getMessage());
            $this->logger->addPushBookingException($e->getMessage(), $pushBooking, $this);

            throw new CmHubException($e->getMessage(), (int) $e->getCode());
        }
    }

    /**
     *
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
        $bookingCollection = $this->bookingCollectionFactory->create();

        return json_encode($this->bookingCollectionNormalizer->normalize($bookingCollection->addBooking($booking)));
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return 'application/json';
    }
}

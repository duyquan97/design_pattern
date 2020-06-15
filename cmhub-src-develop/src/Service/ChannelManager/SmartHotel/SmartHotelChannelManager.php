<?php

namespace App\Service\ChannelManager\SmartHotel;

use App\Entity\Booking;
use App\Exception\ChannelManagerClientException;
use App\Exception\CmHubException;
use App\Booking\Model\BookingStatus;
use App\Model\CredentialInterface;
use App\Model\PushBooking;
use App\Model\RatePlanCode;
use App\Service\ChannelManager\AbstractChannelManager;
use App\Service\ChannelManager\ChannelManagerInterface;
use App\Service\ChannelManager\ChannelManagerList;
use App\Utils\HttpClient;
use App\Utils\Monolog\CmhubLogger;
use GuzzleHttp\Exception\GuzzleException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class SmartHotelChannelManager
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class SmartHotelChannelManager extends AbstractChannelManager implements ChannelManagerInterface
{

    public const NAME = ChannelManagerList::SMARTHOTEL;
    private const BOOKING_CONFIRMED_URI = 'OTA_HotelResNotifRQ.aspx';
    private const BOOKING_CANCELLED_URI = 'OTA_CancelRQ.aspx';

    /**
     * @var CredentialInterface
     */
    private $credentials;

    /**
     * SmartHotelChannelManager constructor.
     *
     * @param HttpClient          $smarthotelHttpClient
     * @param CredentialInterface $smarthotelCredentials
     * @param Environment         $templating
     * @param CmhubLogger         $logger
     */
    public function __construct(HttpClient $smarthotelHttpClient, CredentialInterface $smarthotelCredentials, Environment $templating, CmhubLogger $logger)
    {
        parent::__construct($smarthotelHttpClient, $templating, $logger);
        $this->credentials = $smarthotelCredentials;
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
     *
     * @param PushBooking $pushBooking
     *
     * @return void
     *
     * @throws ChannelManagerClientException
     * @throws CmHubException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function pushBookingRequest(PushBooking $pushBooking): void
    {
        $booking = $pushBooking->getBooking();
        $endpoint = self::BOOKING_CANCELLED_URI;
        if ($booking->getStatus() === BookingStatus::CONFIRMED) {
            $endpoint = self::BOOKING_CONFIRMED_URI;
        }
        $request = $this->getRequestBody($booking);
        try {
            $booking->getTransaction()->setRequest($request);
            $response = $this->httpClient->call('POST', $request, [], $endpoint);
            $responseContent = $response->getBody()->getContents();
            $booking->getTransaction()->setResponse($responseContent);
            $this
                ->logger
                ->addPushBookingSent(
                    $pushBooking,
                    $request,
                    $responseContent
                );
        } catch (GuzzleException $ex) {
            $booking->getTransaction()->setResponse($ex->getMessage());

            throw new ChannelManagerClientException($ex->getMessage(), (int) $ex->getCode());
        }

        libxml_use_internal_errors(true);
        $contentsResponse = simplexml_load_string($responseContent, "SimpleXMLElement", LIBXML_NOCDATA);

        if (false === $contentsResponse) {
            $message = sprintf('wrong format response "%s"', $responseContent);
            $this->logger->addPushBookingException($message, $pushBooking, $this, $request);
            throw new CmHubException($message, 400);
        }

        if ($contentsResponse instanceof \SimpleXMLElement && isset($contentsResponse->Errors->Error)) {
            $message = (string) $contentsResponse->children()->Errors->children()->Error->children()->Text;
            $this->logger->addPushBookingException($message, $pushBooking, $this, $request);
            throw new CmHubException($message, 400);
        }
    }

    /**
     *
     * @param Booking $booking
     *
     * @return string
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getRequestBody(Booking $booking): string
    {
        $apiKey = $this->credentials->getUsername();
        $apiPassword = $this->credentials->getPassword();

        if ($booking->getStatus() === BookingStatus::CONFIRMED) {
            return $this->templating->render(
                'Api/Ext/Xml/SmartHotel/V1/PushBooking.xml.twig',
                [
                    'apiKey'      => $apiKey,
                    'apiPassword' => $apiPassword,
                    'booking'     => $booking,
                    'ratePlan'    => RatePlanCode::SBX,
                    'customer'    => current($booking->getGuests()),
                ]
            );
        }

        return $this->templating->render(
            'Api/Ext/Xml/SmartHotel/V1/CancelBooking.xml.twig',
            [
                'apiKey'      => $apiKey,
                'apiPassword' => $apiPassword,
                'booking'     => $booking,
            ]
        );
    }
}

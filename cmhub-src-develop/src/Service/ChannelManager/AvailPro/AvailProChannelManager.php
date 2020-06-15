<?php declare(strict_types=1);

namespace App\Service\ChannelManager\AvailPro;

use App\Entity\Booking;
use App\Exception\ChannelManagerClientException;
use App\Exception\CmHubException;
use App\Model\PushBooking;
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
 * Class AvailPro
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailProChannelManager extends AbstractChannelManager implements ChannelManagerInterface
{
    public const NAME = ChannelManagerList::AVAILPRO;

    /**
     * AvailPro constructor.
     *
     * @param HttpClient  $availproHttpClient
     * @param Environment $templating
     * @param CmhubLogger $logger
     */
    public function __construct(HttpClient $availproHttpClient, Environment $templating, CmhubLogger $logger)
    {
        parent::__construct($availproHttpClient, $templating, $logger);
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
        $xml = $this->getRequestBody($booking);

        try {
            $booking->getTransaction()->setRequest($xml);
            $response = $this->httpClient->call('POST', $xml);
            $responseContent = $response->getBody()->getContents();
            $booking->getTransaction()->setResponse($responseContent);
            $this
                ->logger
                ->addPushBookingSent(
                    $pushBooking,
                    $xml,
                    $responseContent
                );
        } catch (GuzzleException|\Exception $e) {
            $booking->getTransaction()->setResponse($e->getMessage());
            $this->logger->addPushBookingException($e->getMessage(), $pushBooking, $this);
            throw new CmHubException($e->getMessage(), (int) $e->getCode());
        }

        if (false !== strpos($responseContent, 'fail')) {
            $booking->getTransaction()->setResponse($responseContent);
            throw new ChannelManagerClientException($responseContent, 400);
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
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getRequestBody(Booking $booking): string
    {
        return $this->templating->render(
            'Api/Ext/Xml/AvailPro/V1/GetBookings.xml.twig',
            [
                'partner'  => $booking->getPartner(),
                'bookings' => [$booking],
                'ratePlan' => 'SBX',
            ]
        );
    }
}

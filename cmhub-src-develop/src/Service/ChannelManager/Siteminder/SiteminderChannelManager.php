<?php declare(strict_types=1);

namespace App\Service\ChannelManager\Siteminder;

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
use Twig\Error;

/**
 * Class SiteminderChannelManager
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class SiteminderChannelManager extends AbstractChannelManager implements ChannelManagerInterface
{
    public const NAME = ChannelManagerList::SITEMINDER;

    /**
     * @var CredentialInterface
     */
    private $credentials;

    /**
     * SiteminderChannelManager constructor.
     *
     * @param HttpClient          $siteminderHttpClient
     * @param CredentialInterface $siteminderCredentials
     * @param Environment         $templating
     * @param CmhubLogger         $logger
     */
    public function __construct(HttpClient $siteminderHttpClient, CredentialInterface $siteminderCredentials, Environment $templating, CmhubLogger $logger)
    {
        parent::__construct($siteminderHttpClient, $templating, $logger);

        $this->credentials = $siteminderCredentials;
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
     * @throws CmHubException
     * @throws Error\LoaderError
     * @throws Error\RuntimeError
     * @throws Error\SyntaxError
     * @throws ChannelManagerClientException
     */
    public function pushBookingRequest(PushBooking $pushBooking): void
    {
        $booking = $pushBooking->getBooking();
        $request = $this->getRequestBody($booking);

        try {
            $booking->getTransaction()->setRequest($request);
            $response = $this->httpClient->call('POST', $request);
            $responseContent = $response->getBody()->getContents();
            $booking->getTransaction()->setResponse($responseContent);

            $this
                ->logger
                ->addPushBookingSent(
                    $pushBooking,
                    $request,
                    $responseContent
                );
        } catch (GuzzleException $e) {
            $booking->getTransaction()->setResponse($e->getMessage());
            $this->logger->addPushBookingException($e->getMessage(), $pushBooking, $this, $request);
            throw new ChannelManagerClientException($e->getMessage(), (int) $e->getCode());
        }

        $errors = strpos($responseContent, 'Error');
        if ($errors) {
            $this->logger->addPushBookingException($responseContent, $pushBooking, $this, $request);

            throw new ChannelManagerClientException($responseContent, $response->getStatusCode());
        }

        $success = strpos(strtolower($responseContent), 'success');
        if (!$success) {
            $this->logger->addPushBookingException($responseContent, $pushBooking, $this, $request);
            throw new ChannelManagerClientException($responseContent, $response->getStatusCode());
        }
    }

    /**
     *
     * @param Booking $booking
     *
     * @return string
     *
     * @throws Error\LoaderError
     * @throws Error\RuntimeError
     * @throws Error\SyntaxError
     */
    public function getRequestBody(Booking $booking): string
    {
        $username = $this->credentials->getUsername();
        $password = $this->credentials->getPassword();

        if (BookingStatus::CONFIRMED === $booking->getStatus()) {
            return $this->templating->render(
                'Api/Ext/Xml/Siteminder/V1/PushBooking.xml.twig',
                [
                    'username'  => $username,
                    'password'  => $password,
                    'booking'   => $booking,
                    'ratePlan'  => RatePlanCode::SBX,
                    'customer'  => current($booking->getGuests()),
                    'echoToken' => uniqid(),
                ]
            );
        }

        return $this->templating->render(
            'Api/Ext/Xml/Siteminder/V1/CancelBooking.xml.twig',
            [
                'username'  => $username,
                'password'  => $password,
                'booking'   => $booking,
                'ratePlan'  => RatePlanCode::SBX,
                'customer'  => current($booking->getGuests()),
                'echoToken' => uniqid(),
            ]
        );
    }
}

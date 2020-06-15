<?php

namespace App\Service\ChannelManager;

use App\Entity\Booking;
use App\Exception\ChannelManagerClientException;
use App\Exception\CmHubException;
use App\Model\PushBooking;
use App\Utils\HttpClient;
use App\Utils\Monolog\CmhubLogger;
use GuzzleHttp\Exception\GuzzleException;
use Twig\Environment;

/**
 * Class AbstractChannelManager
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
abstract class AbstractChannelManager
{
    /**
     *
     * @var HttpClient
     */
    protected $httpClient;

    /**
     *
     * @var Environment
     */
    protected $templating;

    /**
     *
     * @var CmhubLogger
     */
    protected $logger;

    /**
     *
     * @param HttpClient          $httpClient
     * @param Environment         $templating
     * @param CmhubLogger         $logger
     */
    public function __construct(HttpClient $httpClient, Environment $templating, CmhubLogger $logger)
    {
        $this->httpClient = $httpClient;
        $this->templating = $templating;
        $this->logger = $logger;
    }

    /**
     *
     * @param PushBooking $pushBooking
     *
     * @return void
     *
     * @throws ChannelManagerClientException
     * @throws CmHubException
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
            $this->logger->addPushBookingException($e->getMessage(), $pushBooking, $this, $request);
            $booking->getTransaction()->setResponse($e->getMessage());
            throw new ChannelManagerClientException($e->getMessage(), (int) $e->getCode());
        }

        libxml_use_internal_errors(true);
        $contents = simplexml_load_string($responseContent, "SimpleXMLElement", LIBXML_NOCDATA);

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

        if (false === $contents) {
            $message = sprintf('wrong format response "%s"', $responseContent);
            $this->logger->addPushBookingException($message, $pushBooking, $this, $request);
            throw new CmHubException($message, 400);
        }

        if ($contents instanceof \SimpleXMLElement && isset($contents->Errors->Error)) {
            $message = (string) $contents->children()->Errors->children()->Error->children()->Text;
            $this->logger->addPushBookingException($message, $pushBooking, $this, $request);
            throw new CmHubException($message, 400);
        }
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return 'application/xml';
    }

    /**
     * @param Booking $booking
     *
     * @return string
     */
    abstract public function getRequestBody(Booking $booking): string;
}

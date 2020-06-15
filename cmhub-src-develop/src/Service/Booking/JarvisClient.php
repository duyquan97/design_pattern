<?php

namespace App\Service\Booking;

use App\Exception\JarvisClientException;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogType;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class JarvisClient
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class JarvisClient
{
    private const STATUS_FAILED = 'failed';
    private const STATUS_SUCCESS = 'success';
    /**
     *
     * @var Client
     */
    private $httpClient;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     * JarvisClient constructor.
     *
     * @param Client $jarvisHttpClient
     * @param CmhubLogger $logger
     *
     */
    public function __construct(Client $jarvisHttpClient, CmhubLogger $logger)
    {
        $this->httpClient = $jarvisHttpClient;
        $this->logger = $logger;
    }

    /**
     * @param string $identifier
     *
     * @return mixed|string
     *
     * @throws JarvisClientException
     *
     */
    public function getBookingDetails(string $identifier)
    {
        return $this->fetch('GET', '/api/rest/jarvis/v1/booking', ['externalId' => $identifier]);
    }


    /**
     * @param string $method
     * @param string|null $uri
     * @param array $data
     *
     * @return mixed|string
     *
     * @throws JarvisClientException
     *
     */
    public function fetch(string $method = 'GET', string $uri = null, array $data = [])
    {
        $this->logger->addRecord(
            \Monolog\Logger::NOTICE,
            sprintf('Jarvis service: requesting %s', $uri),
            [
                LogKey::TYPE_KEY => LogType::JARVIS,
                LogKey::ENDPOINT_KEY => $uri,
                LogKey::REQUEST_KEY => json_encode($data),
            ],
            $this
        );

        $timeStart = microtime(true);

        try {
            $params = $method === 'GET' ? ['query' => $data] : ['data' => $data];

            $httpResponse = $this->httpClient->request('GET', $uri, $params);
        } catch (GuzzleException | \Exception $exception) {
            $this->logger->addRecord(
                \Monolog\Logger::NOTICE,
                sprintf('Jarvis service: requesting %s', LogType::JARVIS),
                [
                    LogKey::TYPE_KEY => LogType::JARVIS,
                    LogKey::ENDPOINT_KEY => $uri,
                    LogKey::STATUS_KEY => static::STATUS_FAILED,
                    LogKey::RESPONSE_KEY => $exception->getMessage(),
                    LogKey::REQUEST_KEY => json_encode($data),
                ],
                $this
            );

            throw new JarvisClientException(
                'JARVIS error',
                500,
                $exception->getMessage()
            );
        }

        $response = $httpResponse->getBody()->getContents();

        $this->logger->addRecord(\Monolog\Logger::DEBUG, $response, [], $this);
        $response = json_decode($response);

        if (!isset($response->id)) {
            $this
                ->logger
                ->addRecord(
                    \Monolog\Logger::INFO,
                    'Jarvis API Request',
                    [
                        LogKey::TYPE_KEY          => LogType::JARVIS,
                        LogKey::STATUS_KEY        => self::STATUS_FAILED,
                        LogKey::ACTION_KEY        => LogAction::API_REQUEST,
                        LogKey::ENDPOINT_KEY      => $uri,
                        LogKey::RESPONSE_KEY      => json_encode($response),
                        LogKey::RESPONSE_TIME_KEY => microtime(true) - $timeStart,
                    ],
                    $this
                );

            throw $this->handleFailedResponse($response);
        }

        $this
            ->logger
            ->addRecord(
                \Monolog\Logger::INFO,
                'Jarvis API Request',
                [
                    LogKey::TYPE_KEY          => LogType::JARVIS,
                    LogKey::STATUS_KEY        => self::STATUS_SUCCESS,
                    LogKey::ACTION_KEY        => LogAction::API_REQUEST,
                    LogKey::ENDPOINT_KEY      => $uri,
                    LogKey::RESPONSE_KEY      => json_encode($response),
                    LogKey::RESPONSE_TIME_KEY => microtime(true) - $timeStart,
                ],
                $this
            );

        return $response;
    }


    /**
     *
     * @param mixed $response
     *
     * @return JarvisClientException
     */
    private function handleFailedResponse($response): JarvisClientException
    {
        return new JarvisClientException(
            'Jarvis Booking Error',
            500,
            json_encode($response)
        );
    }
}

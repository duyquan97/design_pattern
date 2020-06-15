<?php

namespace App\Service\EAI;

use App\Entity\TransactionStatus;
use App\Exception\EAIClientException;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogType;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;

/**
 * Class EAIClient
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class EAIClient
{
    private const API_VERSION = 'v0';
    private const STATUS_FAILED = 'failed';
    private const STATUS_SUCCESS = 'success';

    /**
     *
     * @var Client
     */
    private $eaiHttpClient;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     *
     * @var string
     */
    private $eaiUsername;

    /**
     *
     * @var string
     */
    private $eaiPassword;

    /**
     * EAIClient constructor.
     *
     * @param Client      $eaiHttpClient
     * @param CmhubLogger $logger
     * @param string      $eaiUsername
     * @param string      $eaiPassword
     */
    public function __construct(Client $eaiHttpClient, CmhubLogger $logger, string $eaiUsername, string $eaiPassword)
    {
        $this->eaiHttpClient = $eaiHttpClient;
        $this->logger = $logger;
        $this->eaiUsername = $eaiUsername;
        $this->eaiPassword = $eaiPassword;
    }

    /**
     *
     * @param string $path
     * @param array  $data
     *
     * @return EAIResponse
     *
     * @throws EAIClientException
     */
    public function request(string $path, array $data): EAIResponse
    {
        $this->logger->addRecord(
            Logger::NOTICE,
            sprintf('EAI service: requesting %s', $endpoint = static::API_VERSION . $path),
            [],
            $this
        );

        $request = $this->buildRequest($data);
        $timeStart = microtime(true);
        try {
            $httpResponse = $this->eaiHttpClient->request(
                'POST',
                $endpoint,
                $request
            );
        } catch (GuzzleException|\Exception $exception) {
            $this
                ->logger
                ->addRecord(
                    Logger::ERROR,
                    'EAI Booking Engine error',
                    [
                        LogKey::TYPE_KEY          => LogType::EAI,
                        LogKey::STATUS_KEY        => self::STATUS_FAILED,
                        LogKey::ACTION_KEY        => LogAction::EAI_REQUEST,
                        LogKey::ENDPOINT_KEY      => $endpoint,
                        LogKey::REQUEST_KEY       => $request['body'],
                        LogKey::RESPONSE_KEY      => $exception->getMessage(),
                        LogKey::RESPONSE_TIME_KEY => microtime(true) - $timeStart,
                    ],
                    $this
                );

            throw new EAIClientException(json_encode($data), (string) $exception->getMessage(), $exception->getCode());
        }

        $statusCode = $httpResponse->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new EAIClientException(json_encode($data), (string) $httpResponse->getBody(), $statusCode);
        }

        $response = $this->buildSuccessResponse($data, $httpResponse);

        $this
            ->logger
            ->addRecord(
                Logger::INFO,
                'EAI API Request',
                [
                    LogKey::TYPE_KEY           => LogType::EAI,
                    LogKey::STATUS_KEY         => self::STATUS_SUCCESS,
                    LogKey::ACTION_KEY         => LogAction::EAI_REQUEST,
                    LogKey::ENDPOINT_KEY       => $endpoint,
                    LogKey::REQUEST_KEY        => $request['body'],
                    LogKey::RESPONSE_KEY       => (string) $httpResponse->getBody(),
                    LogKey::TRANSACTION_ID_KEY => $response->getTransactionId(),
                    LogKey::RESPONSE_TIME_KEY  => microtime(true) - $timeStart,
                ],
                $this
            );

        return $response;
    }

    /**
     *
     * @param array $data
     *
     * @return array
     */
    private function buildRequest(array $data): array
    {
        $body = json_encode($data);

        return [
            'body'    => $body,
            'headers' => [
                'content-type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Basic ' . base64_encode(sprintf('%s:%s', $this->eaiUsername, $this->eaiPassword)),
            ],
        ];
    }

    /**
     *
     * @param array             $request
     * @param ResponseInterface $response
     *
     * @return EAIResponse
     */
    private function buildSuccessResponse(array $request, ResponseInterface $response): EAIResponse
    {
        $eaiResponse = new EAIResponse();
        $eaiResponse->setResponse((string) $response->getBody());
        $eaiResponse->setRequest(json_encode($request));

        //Flatten headers array
        $headers = array();
        foreach (array_keys($response->getHeaders()) as $name) {
            $headers[$name] = current($response->getHeader($name));
        }

        $eaiResponse
            ->setHeaders($headers)
            ->setStatus(TransactionStatus::SENT)
            ->setStatusCode($response->getStatusCode());

        return $eaiResponse;
    }
}

<?php

namespace App\Service\Iresa;

use App\Entity\RequestLog;
use App\Exception\IresaClientException;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogType;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class CommonService
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class IresaClient
{
    const API_VERSION = 2;
    const FAILED_RESPONSE = [
        'fail',
        'error',
    ];
    const SUCCESS_RESPONSE = 'Success';
    const ROOM_TYPE_CODE_NOT_FOUND = '[InvalidPrestation]';
    const PARTNER_NOT_FOUND = 'partner not found';

    private const STATUS_FAILED = 'failed';
    private const STATUS_SUCCESS = 'success';

    /**
     *
     * @var Client
     */
    private $iresaHttpClient;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     *
     * @var string
     */
    private $username;

    /**
     *
     * @var string
     */
    private $password;

    /**
     *
     * @var string
     */
    private $token = '';

    /**
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * IresaClient constructor.
     *
     * @param Client                 $iresaHttpClient
     * @param string                 $iresaUsername
     * @param string                 $iresaPassword
     * @param CmhubLogger            $logger
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(Client $iresaHttpClient, string $iresaUsername, string $iresaPassword, CmhubLogger $logger, EntityManagerInterface $entityManager)
    {
        $this->iresaHttpClient = $iresaHttpClient;
        $this->username = $iresaUsername;
        $this->password = $iresaPassword;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    /**
     *
     * @param string $path
     * @param array  $data
     *
     * @return mixed|string
     *
     * @throws IresaClientException
     */
    public function fetch(string $path, array $data)
    {
        $endpoint = $path . static::API_VERSION;
        $request = $this->buildRequest($data);
        $rawRequest = json_encode($request);
        $timeStart = microtime(true);

        try {
            $httpResponse = $this->iresaHttpClient->request(
                'POST',
                $endpoint,
                $request
            );
        } catch (GuzzleException | \Exception $exception) {
            $this->createLog($rawRequest, $exception->getMessage());

            $this->logger
                ->addRecord(
                    \Monolog\Logger::INFO,
                    'Iresa API Request',
                    [
                        LogKey::TYPE_KEY          => LogType::IRESA_TYPE,
                        LogKey::STATUS_KEY        => self::STATUS_FAILED,
                        LogKey::ACTION_KEY        => LogAction::API_REQUEST,
                        LogKey::ENDPOINT_KEY      => $endpoint,
                        LogKey::REQUEST_KEY       => $rawRequest,
                        LogKey::RESPONSE_KEY      => $exception->getMessage(),
                        LogKey::RESPONSE_TIME_KEY => microtime(true) - $timeStart,
                    ],
                    $this
                );

            throw new IresaClientException(
                'Booking Engine error',
                500,
                $exception->getMessage()
            );
        }

        $rawResponse = $httpResponse->getBody()->getContents();

        $response = json_decode($rawResponse);

        if ($response->status !== 'success') {
            $this->createLog($rawRequest, $rawResponse);

            $this
                ->logger
                ->addRecord(
                    \Monolog\Logger::INFO,
                    'Iresa API Request',
                    [
                        LogKey::TYPE_KEY          => LogType::IRESA_TYPE,
                        LogKey::STATUS_KEY        => self::STATUS_FAILED,
                        LogKey::ACTION_KEY        => LogAction::API_REQUEST,
                        LogKey::ENDPOINT_KEY      => $path . static::API_VERSION,
                        LogKey::REQUEST_KEY       => $rawRequest,
                        LogKey::RESPONSE_KEY      => $rawResponse,
                        LogKey::RESPONSE_TIME_KEY => microtime(true) - $timeStart,
                    ],
                    $this
                );

            throw $this->handleFailedResponse($response);
        }

        $this->token = $response->tokenLoginSession ?? '';

        $this
            ->logger
            ->addRecord(
                \Monolog\Logger::INFO,
                'Iresa API Request',
                [
                    LogKey::TYPE_KEY          => LogType::IRESA_TYPE,
                    LogKey::STATUS_KEY        => self::STATUS_SUCCESS,
                    LogKey::ACTION_KEY        => LogAction::API_REQUEST,
                    LogKey::ENDPOINT_KEY      => $path . static::API_VERSION,
                    LogKey::REQUEST_KEY       => $rawRequest,
                    LogKey::RESPONSE_KEY      => $rawResponse,
                    LogKey::RESPONSE_TIME_KEY => microtime(true) - $timeStart,
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
        $request = json_encode(
            [
                'ssoQ'             => $this->username,
                'ssoP'             => $this->password,
                'tokenUserSession' => $this->token,
                'data'             => $data,
            ]
        );

        return [
            'body'    => $request,
            'headers' => [
                'content-type' => 'application/json',
                'Accept'       => 'application/json',
            ],
        ];
    }

    /**
     *
     * @param \StdClass $response
     *
     * @return IresaClientException
     */
    private function handleFailedResponse(\StdClass $response): IresaClientException
    {
        return new IresaClientException(
            'Booking Engine error',
            500,
            json_encode($response)
        );
    }

    /**
     *
     * @param string $request
     * @param string $response
     *
     * @return void
     */
    private function createLog(string $request, string $response)
    {
        $log = (new RequestLog())
            ->setRequest($request)
            ->setResponse($response);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}

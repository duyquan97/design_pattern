<?php

namespace App\Service\ChannelManager;

use App\Exception\ChannelManagerClientException;
use App\Utils\Monolog\CmhubLogger;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class CommonService
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ChannelManagerClient
{
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
     * ChannelManagerClient constructor.
     *
     * @param Client      $httpClient
     * @param CmhubLogger $logger
     */
    public function __construct(Client $httpClient, CmhubLogger $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     *
     * @param string $path
     * @param string $data
     *
     * @return \stdClass
     *
     * @throws GuzzleException
     * @throws ChannelManagerClientException
     */
    public function call(string $path, string $data)
    {
        $httpResponse = $this->httpClient->request(
            'POST',
            $path,
            $data
        );

        if (!$httpResponse) {
            throw new ChannelManagerClientException();
        }

        $this->logger->addRecord(\Monolog\Logger::DEBUG, $httpResponse, [], $this);

        return $httpResponse;
    }
}

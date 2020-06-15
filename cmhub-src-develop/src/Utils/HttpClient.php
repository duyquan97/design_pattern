<?php

namespace App\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class HttpClient
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class HttpClient
{
    /**
     *
     * @var Client
     */
    private $client;

    /**
     * HttpClient constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     *
     * @param string $method
     * @param string $data
     * @param array  $headers
     * @param string $uri
     *
     * @return ResponseInterface
     *
     * @throws GuzzleException
     */
    public function call(string $method = 'GET', string $data = '', array $headers = [], string $uri = '')
    {
        return $this
            ->client
            ->request(
                $method,
                $uri,
                [
                    'body'    => $data,
                    'headers' => $headers,
                ]
            );
    }
}

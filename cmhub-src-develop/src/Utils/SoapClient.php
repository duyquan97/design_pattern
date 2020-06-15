<?php

namespace App\Utils;

/**
 * Class SoapClient
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class SoapClient
{
    /**
     *
     * @var \SoapClient
     */
    private $client;

    /**
     * SoapClient constructor.
     *
     * @param \SoapClient $client
     */
    public function __construct(\SoapClient $client)
    {
        $this->client = $client;
    }

    /**
     *
     * @param string     $functionName
     * @param array      $arguments
     * @param array|null $options
     * @param null       $inputHeaders
     * @param array|null $outputHeaders
     *
     * @return mixed
     */
    public function call(string $functionName, array $arguments, array $options = null, $inputHeaders = null, array &$outputHeaders = null)
    {
        return $this->client->__soapCall($functionName, $arguments, $options, $inputHeaders, $outputHeaders);
    }

    /**
     *
     * @return string
     */
    public function getLastRequest()
    {
        return $this->client->__getLastRequest();
    }
}

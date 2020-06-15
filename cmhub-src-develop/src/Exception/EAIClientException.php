<?php

namespace App\Exception;

/**
 * Class EAIClientException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class EAIClientException extends CmHubException
{
    public const TYPE = 'eai_client';

    /**
     * @var string
     */
    private $request;

    /**
     * @var string
     */
    private $response;

    /**
     * @var string
     */
    private $statusCode;


    /**
     * EAIClientException constructor.
     *
     * @param string $request
     * @param string $response
     * @param string $statusCode
     */
    public function __construct(string $request, string $response, string $statusCode)
    {
        $this->request = $request;
        $this->response = $response;
        $this->statusCode = $statusCode;

        parent::__construct('', 0);
    }

    /**
     *
     * @return string
     */
    public function getExceptionType(): string
    {
        return static::TYPE;
    }

    /**
     *
     * @return string
     */
    public function getRequest(): string
    {
        return $this->request;
    }

    /**
     *
     * @return string
     */
    public function getResponse(): string
    {
        return $this->response;
    }

    /**
     *
     * @return string
     */
    public function getStatusCode(): string
    {
        return $this->statusCode;
    }
}

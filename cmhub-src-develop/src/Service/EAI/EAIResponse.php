<?php

namespace App\Service\EAI;

/**
 * Class EAIResponse
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class EAIResponse
{
    public const TRANSACTION_ID = "X-Transaction-Id";

    /**
     * @var array
     */
    private $headers = array();

    /**
     * @var string
     */
    private $request;

    /**
     * @var int
     */
    private $statusCode;

    /**
     *
     * @var string
     */
    private $response;

    /**
     * @var string
     */
    private $status;

    /**
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     *
     * @param array $headers
     *
     * @return EAIResponse
     */
    public function setHeaders(array $headers): EAIResponse
    {
        $this->headers = $headers;

        return $this;
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
     * @param string $request
     *
     * @return EAIResponse
     */
    public function setRequest(string $request): EAIResponse
    {
        $this->request = $request;

        return $this;
    }

    /**
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     *
     * @param int $statusCode
     *
     * @return EAIResponse
     */
    public function setStatusCode(int $statusCode): EAIResponse
    {
        $this->statusCode = $statusCode;

        return $this;
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
     * @param string $response
     *
     * @return EAIResponse
     */
    public function setResponse(string $response): EAIResponse
    {
        $this->response = $response;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     *
     * @param string $status
     *
     * @return EAIResponse
     */
    public function setStatus(string $status): EAIResponse
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTransactionId(): ?string
    {
        if (isset($this->headers[self::TRANSACTION_ID])) {
            return $this->headers[self::TRANSACTION_ID];
        }

        return null;
    }
}

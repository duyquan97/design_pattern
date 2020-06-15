<?php

namespace App\Exception;

/**
 * Class JarvisClientException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class JarvisClientException extends \Exception
{
    const TYPE = 'jarvis_client';

    /**
     * @var string
     */
    private $response;

    /**
     * IresaClientException constructor.
     *
     * @param string $message
     * @param int    $code
     * @param string $response
     */
    public function __construct(string $message, int $code, string $response)
    {
        parent::__construct($message, $code);

        $this->response = $response;
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
     * @return string
     */
    public function getResponse(): string
    {
        return $this->response;
    }
}

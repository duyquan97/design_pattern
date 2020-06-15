<?php declare(strict_types=1);

namespace App\Exception;

/**
 * Class CmHubException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class CmHubException extends \Exception
{
    const TYPE = 'cmhub';

    /**
     *
     * @param string $message
     *
     * @return CmHubException
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     *
     * @param int $code
     *
     * @return CmHubException
     */
    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getExceptionType(): string
    {
        return static::TYPE;
    }
}

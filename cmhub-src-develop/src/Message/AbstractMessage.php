<?php

namespace App\Message;

/**
 * Class AbstractMessage
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
abstract class AbstractMessage implements CorrelatedIdInterface
{
    /**
     *
     * @var string
     */
    protected $correlationId;

    /**
     *
     * @return string
     */
    public function getCorrelationId(): string
    {
        return $this->correlationId;
    }

    /**
     *
     * @param string $correlationId
     *
     * @return AbstractMessage
     */
    public function setCorrelationId(string $correlationId): AbstractMessage
    {
        $this->correlationId = $correlationId;

        return $this;
    }
}

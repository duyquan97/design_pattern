<?php

namespace App\Message;

/**
 * Interface CorrelatedIdInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface CorrelatedIdInterface
{
    /**
     *
     * @param string $correlationId
     *
     * @return $this
     */
    public function setCorrelationId(string $correlationId);

    /**
     *
     * @return string
     */
    public function getCorrelationId(): string;
}

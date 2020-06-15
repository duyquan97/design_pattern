<?php

namespace App\Message;

/**
 * Class TransactionScheduled
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class TransactionScheduled extends AbstractMessage
{
    /**
     * @var string The transaction identifier
     */
    private $identifier;


    /**
     * TransactionScheduled constructor.
     *
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     *
     * @return string
     */
    public function getCorrelationId(): string
    {
        return $this->correlationId;
    }
}

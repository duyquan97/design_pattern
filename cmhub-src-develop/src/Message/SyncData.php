<?php

namespace App\Message;

/**
 * Class SyncData
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class SyncData extends AbstractMessage
{
    /**
     * @var string The transaction identifier
     */
    private $identifier;

    /**
     * @var \DateTime $start
     */
    private $start;

    /**
     * @var \DateTime $end
     */
    private $end;

    /**
     * @var string $type
     */
    private $type;

    /**
     * TransactionScheduled constructor.
     *
     * @param string    $identifier
     * @param \DateTime $start
     * @param \DateTime $end
     * @param string    $type
     *
     */
    public function __construct(string $identifier, \DateTime $start, \DateTime $end, string $type)
    {
        $this->identifier = $identifier;
        $this->start = $start;
        $this->end = $end;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return \DateTime
     */
    public function getStart(): \DateTime
    {
        return $this->start;
    }

    /**
     * @return \DateTime
     */
    public function getEnd(): \DateTime
    {
        return $this->end;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}

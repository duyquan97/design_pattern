<?php

namespace App\Message;

/**
 * Class MasterProductUpdated
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class MasterProductUpdated extends AbstractMessage
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * MasterProductUpdated constructor.
     *
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}

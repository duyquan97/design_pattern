<?php

namespace App\Message;

/**
 * Class PartnerUpdated
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PartnerUpdated extends AbstractMessage
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

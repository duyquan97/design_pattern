<?php

namespace App\Message;

/**
 * Class PartnerChannelManagerUpdated
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PartnerChannelManagerUpdated extends AbstractMessage
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * PartnerChannelManagerUpdated constructor.
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
}

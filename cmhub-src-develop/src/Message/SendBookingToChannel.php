<?php

namespace App\Message;

/**
 * Class SendBookingToChannel
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class SendBookingToChannel extends AbstractMessage
{
    /**
     * @var string The booking reservation id.
     */
    private $identifier;

    /**
     * SendBookingToChannel constructor.
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

<?php

namespace App\Message;

/**
 * Class BookingCreated
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingCreated extends AbstractMessage
{

    /**
     * @var string The booking reservation id.
     */
    private $identifier;

    /**
     * BookingCreated constructor.
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

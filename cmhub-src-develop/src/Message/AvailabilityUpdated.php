<?php

namespace App\Message;

/**
 * Class AvailabilityUpdated
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailabilityUpdated extends AbstractMessage
{
    /**
     * @var array
     */
    private $availabilityIds;

    /**
     * @var string
     */
    private $channel;

    /**
     * AvailabilityUpdated constructor.
     *
     * @param array  $availabilityIds
     * @param string $channel
     */
    public function __construct(array $availabilityIds, string $channel)
    {
        $this->availabilityIds = $availabilityIds;
        $this->channel = $channel;
    }

    /**
     *
     * @return array
     */
    public function getAvailabilityIds(): array
    {
        return $this->availabilityIds;
    }

    /**
     *
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }
}

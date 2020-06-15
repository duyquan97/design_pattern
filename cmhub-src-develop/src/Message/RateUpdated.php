<?php

namespace App\Message;

/**
 * Class RateUpdated
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class RateUpdated extends AbstractMessage
{
    /**
     * @var array
     */
    private $rateIds;

    /**
     * @var string
     */
    private $channel;

    /**
     * RateUpdated constructor.
     *
     * @param array $rateIds
     * @param string $channel
     */
    public function __construct(array $rateIds, string $channel)
    {
        $this->rateIds = $rateIds;
        $this->channel = $channel;
    }

    /**
     * @return array
     */
    public function getRateIds(): array
    {
        return $this->rateIds;
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }
}

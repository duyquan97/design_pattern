<?php

namespace App\Message;

/**
 * Class PullRoom
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PullRoom extends AbstractMessage
{
    /**
     * @var string
     */
    private $partnerId;

    /**
     * PullRoom constructor.
     *
     * @param string $partnerId
     */
    public function __construct(string $partnerId)
    {
        $this->partnerId = $partnerId;
    }

    /**
     * @return string
     */
    public function getPartnerId(): string
    {
        return $this->partnerId;
    }
}

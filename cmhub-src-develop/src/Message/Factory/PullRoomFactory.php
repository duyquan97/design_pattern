<?php

namespace App\Message\Factory;

use App\Message\PullRoom;

/**
 * Class PullRoomFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PullRoomFactory
{
    /**
     * @param string $partnerId
     *
     * @return PullRoom
     */
    public function create(string $partnerId)
    {
        return new PullRoom($partnerId);
    }
}

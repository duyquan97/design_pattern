<?php

namespace App\Model\Factory;

use App\Model\Guest;

/**
 * Class GuestFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class GuestFactory
{
    /**
     *
     * @return Guest
     */
    public function create(): Guest
    {
        return new Guest();
    }
}

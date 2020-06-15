<?php

namespace App\Model;

/**
 * Class RestrictionType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class RestrictionType
{
    public const MASTER = 'Master';
    public const ARRIVAL = 'Arrival';
    public const ALL = [self::MASTER, self::ARRIVAL];
}

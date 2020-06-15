<?php

namespace App\Model;

/**
 * Class UniverseIdType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class UniverseIdType
{
    public const STA = 'STA';
    public const STG = 'STG';
    public const STW = 'STW';
    public const EXPERIENCE_TYPES = [self::STA, self::STG, self::STW];
}

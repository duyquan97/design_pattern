<?php

namespace App\Utils;

/**
 * Class OTAAgeFormatter
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class OTAAgeFormatter
{
    public const DEFAULT_AGE_QUALIFYING_CODE = 10;

    /**
     *
     * @param int $age
     *
     * @return int
     */
    public static function format(int $age): int
    {
        if ($age <= 0) {
            return 1;
        }

        if ($age < 3) {
            return 7;
        }

        if ($age < 12) {
            return 8;
        }

        if ($age < 65) {
            return 10;
        }

        // $age >= 65
        return 11;
    }
}

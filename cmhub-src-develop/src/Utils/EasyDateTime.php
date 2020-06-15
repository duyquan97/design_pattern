<?php

namespace App\Utils;

/**
 * Class EasyDateTime
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class EasyDateTime
{
    /**
     *
     * @param string $date
     * @param array  $formats
     *
     * @return bool|\DateTime
     */
    public static function createFromFormats(string $date, array $formats)
    {
        foreach ($formats as $format) {
            $datetime = \DateTime::createFromFormat($format, $date);
            if ($datetime) {
                return $datetime;
            }
        }

        return false;
    }
}

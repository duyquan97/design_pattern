<?php

namespace App\Utils;

/**
 * Class Util
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class Util
{
    /**
     *
     * @param mixed $value
     *
     * @return array
     */
    public static function toArray($value)
    {
        return is_array($value) ? $value : [$value];
    }

    /**
     *
     * @param string $camelCaseString
     *
     * @return string
     */
    public static function toSnakeCase(string $camelCaseString): string
    {
        return strtolower(
            preg_replace(
                [
                    "/([A-Z]+)/",
                    "/_([A-Z]+)([A-Z][a-z])/",
                ],
                [
                    "_$1",
                    "_$1_$2",
                ],
                lcfirst($camelCaseString)
            )
        );
    }
}

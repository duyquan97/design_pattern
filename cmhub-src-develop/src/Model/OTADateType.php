<?php

namespace App\Model;

/**
 * Class OTADateType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class OTADateType
{
    public const ARRIVAL_DATE = 'ArrivalDate';
    public const DEPARTURE_DATE = 'DepartureDate';
    public const CREATE_DATE = 'CreateDate';
    public const LAST_UPDATE_DATE = 'LastUpdateDate';

    public const DATE_TYPE = [
        self::ARRIVAL_DATE,
        self::DEPARTURE_DATE,
        self::CREATE_DATE,
        self::LAST_UPDATE_DATE,
    ];
}

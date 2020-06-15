<?php

namespace App\Utils\Monolog;

/**
 * Class LogAction
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
final class LogAction
{
    public const DB_INSERT   = 'db_insert';
    public const DB_UPDATE   = 'db_update';
    public const DB_DELETE   = 'db_delete';
    public const IMPORT_DATA = 'import_data';

    public const API_REQUEST = 'api_request';


    public const GET_BOOKINGS   = 'get_bookings';
    public const GET_DATA       = 'get_data';
    public const GET_RATES      = 'get_rates';
    public const GET_RATE_PLANS = 'get_rate_plans';
    public const GET_PRODUCTS   = 'get_products';
    public const GET_PRICES     = 'get_prices';
    public const UPDATE_DATA    = 'update_data';

    public const UPDATE_AVAILABILITY = 'stock_updated';
    public const UPDATE_RATES        = 'rates_updated';
    public const EAI_UPDATE_CALLBACK = 'transaction_status_updated';
    public const GET_AVAILABILITY    = 'get_stock';
    public const PING                = 'ping';


    public const CM_OPERATION = 'cm_op';
    public const PARTNER_FLOW = 'partner_flow';
    public const PRODUCT_FLOW = 'product_flow';
    public const PRICE_FLOW = 'price_flow';
    public const PUSH_BOOKING = 'push_booking';
    public const EAI_REQUEST  = 'eai_request';

    public const PUSH_BOOKING_SENT = 'booking_sent';
    public const PUSH_BOOKING_FAILED = 'booking_failed';
    public const LOG_CREATE   = 'create';
    public const LOG_UPDATE   = 'update';
    public const LOG_DELETE   = 'delete';

    public const R2D2_GET_AVAILABILITY   = 'r2d2_get_availability';
    public const R2D2_GET_PRICE          = 'r2d2_get_price';

    public const DISCREPANCY_AVAILABILITY = 'availability_discrepancy';
    public const DISCREPANCY_PRICE        = 'price_discrepancy';
}

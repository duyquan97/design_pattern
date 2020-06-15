<?PHP

namespace App\Utils\Monolog;

/**
 * Class LogKey
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
final class LogKey
{
    public const TYPE_KEY                       = 'type';
    public const ENTITY_KEY                     = 'entity';
    public const STATUS_KEY                     = 'status';
    public const ACTION_KEY                     = 'action';
    public const ENDPOINT_KEY                   = 'endpoint';
    public const REQUEST_KEY                    = 'request';
    public const RESPONSE_KEY                   = 'response';
    public const TRANSACTION_ID_KEY             = 'transaction_id';
    public const INTERNAL_ID                    = 'id';
    public const TRANSACTION_TYPE_KEY           = 'transaction_type';
    public const LINE_KEY                       = 'line';
    public const TRACE_KEY                      = 'trace';
    public const RESPONSE_TIME_KEY              = 'response_time';
    public const PARTNER_ID_KEY                 = 'partner_id';
    public const PARTNER_NAME_KEY               = 'partner_name';
    public const HOST_KEY                       = 'host';
    public const CONTENT_TYPE_KEY               = 'content_type';
    public const SCHEME_KEY                     = 'scheme';
    public const CLIENT_IP_KEY                  = 'client_ip';
    public const START_DATE                     = 'start_date';
    public const END_DATE                       = 'end_date';

    public const MY_PID = 'mypid';

    public const MESSENGER_EVENT          = 'event';
    public const MESSENGER_MESSAGE_TYPE   = 'message_type';
    public const MESSENGER_RECEIVER_NAME  = 'receiver_name';

    public const EX_TYPE_KEY        = 'ex_type';
    public const MESSAGE_KEY        = 'message';
    public const USERNAME_KEY       = 'username';
    public const CM_KEY             = 'cm';
    public const PARTNER_KEY        = 'partner';
    public const EXPERIENCE_KEY     = 'experience';
    public const CHANNEL_KEY        = 'target';
    public const USER_KEY           = 'user';
    public const OPERATION_KEY      = 'op';
    public const BOOKING_STATUS_KEY = 'booking_status';
    public const AMOUNT             = 'amount';
    public const CURRENCY           = 'currency';
    public const COMMENT            = 'comment';
    public const VOUCHER_NUMBER     = 'voucher_number';

    public const IDENTIFIER_KEY     = 'identifier';

    public const MAX_MEMORY_USAGE_KEY  = 'max_memory_usage';
    public const EXECUTION_TIME_KEY    = 'execution_time';
    public const URI_KEY               = 'uri';
    public const CONTROLLER_METHOD_KEY = 'controller_method';
    public const VALIDATION_ERRORS     = 'errors';
    public const FIELD                 = 'field';
    public const FIELD_ERROR           = 'field_error';
    public const STATUS_CODE_KEY       = 'status_code';
    public const RETRIES_KEY           = 'retries';
    public const SENT_AT_KEY           = 'sent_at';
    public const CREATED_AT_KEY        = 'created_at';
    public const UPDATED_AT_KEY        = 'updated_at';
    public const QUANTITY_KEY          = 'quantity';
    public const PRODUCT_ID_KEY        = 'product_id';
    public const DATE_KEY              = 'date';
    public const STOP_SALE_KEY         = 'stop_sale';
}

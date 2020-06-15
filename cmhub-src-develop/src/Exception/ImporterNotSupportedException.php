<?php

namespace App\Exception;

/**
 * Class ImporterNotSupportedException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ImporterNotSupportedException extends \Exception
{
    public const MESSAGE = 'The import data type "%s" is not supported';
    public const TYPE = 'importer_not_supported';

    /**
     * RoomCodeNotFoundException constructor.
     *
     * @param string $importDataType
     */
    public function __construct(string $importDataType)
    {
        parent::__construct(sprintf(static::MESSAGE, $importDataType), 500);
    }

    /**
     *
     * @return string
     */
    public function getExceptionType(): string
    {
        return static::TYPE;
    }
}

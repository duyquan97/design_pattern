<?php

namespace App\Utils;

use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\ResultSet;
use League\Csv\Statement;

/**
 * Class CsvReader
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 *
 */
class CsvReader
{
    /**
     *
     * @param string $filePath
     * @param int    $offset
     *
     * @return array|ResultSet
     *
     * @throws Exception
     */
    public function read(string $filePath, int $offset = 0)
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $reader = Reader::createFromPath($filePath);
        $reader->setHeaderOffset(0);

        return (new Statement())->offset($offset)->process($reader);
    }

    /**
     *
     * @param string $filePath
     * @param int    $offset
     *
     * @return int
     *
     * @throws Exception
     */
    public function count(string $filePath, int $offset = 0)
    {
        $records = $this->read($filePath, $offset);

        return $records->count();
    }
}

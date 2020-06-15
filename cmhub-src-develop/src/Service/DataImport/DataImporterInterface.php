<?php

namespace App\Service\DataImport;

use App\Entity\ImportData;
use App\Service\DataImport\Model\ImportDataRowInterface;

/**
 * Class DataImporterInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface DataImporterInterface
{
    /**
     *
     * @param array $row
     *
     * @return ImportDataRowInterface
     */
    public function process(array $row);

    /**
     * @param ImportData $importData
     *
     * @return bool
     */
    public function supports(ImportData $importData): bool;
}

<?php

namespace App\Service\DataImport\Model;

use App\Entity\ImportData;
use SplDoublyLinkedList;

/**
 * Class ImportDataCollectionInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface ImportDataCollectionInterface
{
    /**
     *
     * @return bool
     */
    public function hasExceptions(): bool;

    /**
     * @return ImportData|null
     */
    public function getImportData(): ?ImportData;

    /**
     * @param ImportData $importData
     *
     * @return self
     */
    public function setImportData(ImportData $importData): self;

    /**
     * @return SplDoublyLinkedList
     */
    public function getRows(): SplDoublyLinkedList;
}

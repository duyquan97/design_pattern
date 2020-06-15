<?php

namespace App\Service\DataImport\Model;

use App\Entity\ImportData;
use SplDoublyLinkedList;

/**
 * Class AvailabilityRowCollection
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailabilityRowCollection implements ImportDataCollectionInterface
{
    /**
     *
     * @var SplDoublyLinkedList
     */
    private $availabilityRows;

    /**
     *
     * @var array
     */
    private $exceptions = [];


    /**
     * @var ImportData
     */
    private $importData;

    /**
     * AvailabilityRowCollection constructor.
     */
    public function __construct()
    {
        $this->availabilityRows = new SplDoublyLinkedList();
    }

    /**
     * @return SplDoublyLinkedList
     */
    public function getRows(): SplDoublyLinkedList
    {
        return $this->availabilityRows;
    }

    /**
     *
     * @param AvailabilityRow $availabilityRow
     *
     * @return self
     */
    public function addAvailabilityRow(AvailabilityRow $availabilityRow): self
    {
        $this->availabilityRows->push($availabilityRow);

        if ($availabilityRow->hasException()) {
            $this->exceptions[] = $availabilityRow->getException();
        }

        return $this;
    }

    /**
     *
     * @return bool
     */
    public function hasExceptions(): bool
    {
        return $this->exceptions ? true : false;
    }

    /**
     *
     * @return array
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    /**
     * @return ImportData|null
     */
    public function getImportData(): ?ImportData
    {
        return $this->importData;
    }

    /**
     * @param ImportData $importData
     *
     * @return self
     */
    public function setImportData(ImportData $importData): ImportDataCollectionInterface
    {
        $this->importData = $importData;

        return $this;
    }
}

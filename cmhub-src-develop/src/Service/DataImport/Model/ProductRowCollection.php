<?php

namespace App\Service\DataImport\Model;

use App\Entity\ImportData;
use SplDoublyLinkedList;

/**
 * Class ProductRowCollection
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductRowCollection implements ImportDataCollectionInterface
{
    /**
     *
     * @var SplDoublyLinkedList
     */
    private $productRows;

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
     * ProductRowCollection constructor.
     */
    public function __construct()
    {
        $this->productRows = new SplDoublyLinkedList();
    }

    /**
     * @return SplDoublyLinkedList
     */
    public function getRows(): SplDoublyLinkedList
    {
        return $this->productRows;
    }

    /**
     *
     * @param ProductRow $productRow
     *
     * @return ProductRowCollection
     */
    public function addProductRow(ProductRow $productRow): ProductRowCollection
    {
        $this->productRows->push($productRow);

        if (!$this->exceptions && $productRow->hasException()) {
            $this->exceptions[] = $productRow->getException();
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

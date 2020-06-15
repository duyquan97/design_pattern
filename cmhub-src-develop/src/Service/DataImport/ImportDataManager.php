<?php

namespace App\Service\DataImport;

use App\Entity\ImportData;
use App\Exception\ImporterNotSupportedException;
use App\Utils\CsvReader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ImportDataManager
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ImportDataManager
{
    /**
     * @var int
     */
    public $batchSize = 100;

    /**
     * @var DataImporterInterface[]
     */
    private $importers;

    /**
     * @var CsvReader
     */
    private $csvReader;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * ImportDataManager constructor.
     *
     * @param CsvReader              $csvReader
     * @param Filesystem             $filesystem
     * @param EntityManagerInterface $entityManager
     * @param array                  $importers
     */
    public function __construct(CsvReader $csvReader, Filesystem $filesystem, EntityManagerInterface $entityManager, array $importers)
    {
        $this->csvReader = $csvReader;
        $this->filesystem = $filesystem;
        $this->entityManager = $entityManager;
        $this->importers = $importers;
    }

    /**
     *
     * @param ImportData $importData
     * @param int        $offset
     *
     * @return \Generator
     *
     * @throws \League\Csv\Exception
     * @throws FileNotFoundException
     * @throws ImporterNotSupportedException
     */
    public function import(ImportData $importData, int $offset = 0)
    {
        if (!$this->filesystem->exists($importData->getPath())) {
            throw new FileNotFoundException(null, 0, null, $importData->getPath());
        }

        $errorsFilename = $importData->getPath() . '.errors';

        $importer = $this->getImporter($importData);
        $records = $this->csvReader->read($importData->getPath(), $offset);
        $index = 1;
        foreach ($records as $record) {
            $row = $importer->process(array_values($record));
            if (null === $row) {
                continue;
            }

            $importData->increaseProcessedRows();

            if ($row->hasException()) {
                $this->filesystem->appendToFile($errorsFilename, $row->getException()->getMessage());
                $importData->increaseError();

                yield $row;

                continue;
            }

            $this->entityManager->persist($row->getEntity());
            $this->entityManager->persist($importData);

            if (($index % $this->batchSize) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();

                $importData = $this->entityManager->merge($importData);
                $this->entityManager->persist($importData);
                $this->entityManager->flush();
            }

            $index++;

            yield $row;
        }


        $importData
            ->setImported(true)
            ->setProcessedRows($index);

        $this->entityManager->flush();
    }

    /**
     *
     * @param ImportData $importData
     * @param int        $offset
     *
     * @return int
     *
     * @throws \League\Csv\Exception
     */
    public function count(ImportData $importData, int $offset = 0)
    {
        return $this->csvReader->count($importData->getPath(), $offset);
    }

    /**
     *
     * @param int $batchSize
     *
     * @return ImportDataManager
     */
    public function setBatchSize(int $batchSize): ImportDataManager
    {
        $this->batchSize = $batchSize;

        return $this;
    }


    /**
     *
     * @param ImportData $importData
     *
     * @return DataImporterInterface
     *
     * @throws ImporterNotSupportedException
     */
    private function getImporter(ImportData $importData): DataImporterInterface
    {
        foreach ($this->importers as $importer) {
            if (!$importer instanceof DataImporterInterface) {
                continue;
            }

            if ($importer->supports($importData)) {
                return $importer;
            }
        }

        throw new ImporterNotSupportedException($importData->getType());
    }
}
